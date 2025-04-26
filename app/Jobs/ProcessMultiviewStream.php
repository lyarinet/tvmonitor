<?php

namespace App\Jobs;

use App\Models\MultiviewLayout;
use App\Models\OutputStream;
use App\Services\FFmpeg\FFmpegService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMultiviewStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected MultiviewLayout $layout;
    protected OutputStream $outputStream;
    protected bool $start;

    /**
     * Create a new job instance.
     */
    public function __construct(MultiviewLayout $layout, OutputStream $outputStream, bool $start = true)
    {
        $this->layout = $layout;
        $this->outputStream = $outputStream;
        $this->start = $start;
    }

    /**
     * Execute the job.
     */
    public function handle(FFmpegService $ffmpegService): void
    {
        try {
            if ($this->start) {
                Log::info("Starting multiview process for layout {$this->layout->id} and output stream {$this->outputStream->id}");
                
                $result = $ffmpegService->startMultiview($this->layout, $this->outputStream);
                
                if ($result) {
                    Log::info("Successfully started multiview process for layout {$this->layout->id} and output stream {$this->outputStream->id}");
                } else {
                    Log::error("Failed to start multiview process for layout {$this->layout->id} and output stream {$this->outputStream->id}");
                }
            } else {
                Log::info("Stopping multiview process for output stream {$this->outputStream->id}");
                
                // First attempt to stop the multiview process via the FFmpegService
                $result = $ffmpegService->stopMultiview($this->outputStream);
                
                // For HLS streams, perform additional verification and cleanup
                if ($this->outputStream->protocol === 'hls') {
                    $this->performHlsCleanupAndVerification();
                }
                
                if ($result) {
                    Log::info("Successfully stopped multiview process for output stream {$this->outputStream->id}");
                } else {
                    // Even if the first stop attempt failed, we've still updated the status
                    // Additional logging of the failure has already been done in FFmpegService
                    Log::warning("Initial stop attempt for output stream {$this->outputStream->id} reported failure - additional steps were attempted");
                    
                    // Ensure we mark the stream as inactive even if stops fail
                    $this->outputStream->refresh(); // Reload to get the latest status
                    if ($this->outputStream->status !== 'inactive') {
                        $this->outputStream->update(['status' => 'inactive']);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error processing multiview stream: {$e->getMessage()}");
            
            // Ensure error info is properly structured
            $errorLog = $this->outputStream->error_log;
            if (!is_array($errorLog)) {
                $errorLog = !empty($errorLog) ? json_decode($errorLog, true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorLog = [];
                }
            }
            
            $this->outputStream->update([
                'status' => 'error',
                'error_log' => array_merge($errorLog, [
                    [
                        'timestamp' => now()->toIso8601String(),
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ]),
            ]);
            
            // Fail the job
            $this->fail($e);
        }
    }
    
    /**
     * Perform additional cleanup and verification for HLS streams
     */
    private function performHlsCleanupAndVerification(): void
    {
        try {
            $outputDir = storage_path("app/public/streams/{$this->outputStream->id}");
            
            if (!file_exists($outputDir)) {
                Log::info("HLS output directory does not exist: {$outputDir}");
                return;
            }
            
            // Check for any active processes writing to the output directory
            exec("lsof +D " . escapeshellarg($outputDir) . " | grep -v grep | awk '{print \$2}' | sort | uniq", $dirPids);
            
            if (!empty($dirPids)) {
                Log::warning("Found processes still writing to HLS directory after stop: " . implode(", ", $dirPids));
                
                // Try to forcefully kill these processes
                foreach ($dirPids as $pid) {
                    if (is_numeric($pid)) {
                        Log::info("Forcefully killing process {$pid} writing to HLS directory");
                        exec("kill -9 {$pid} 2>&1");
                    }
                }
            }
            
            // Verify no new segments are being created
            $segmentCount1 = count(glob("{$outputDir}/segment_*.ts"));
            sleep(3); // Wait to see if new segments are created
            $segmentCount2 = count(glob("{$outputDir}/segment_*.ts"));
            
            if ($segmentCount2 > $segmentCount1) {
                Log::warning("HLS segments are still being created after stopping stream: {$segmentCount1} → {$segmentCount2}");
                
                // Last resort: find and kill any ffmpeg processes
                exec("ps aux | grep ffmpeg | grep -v grep | awk '{print \$2}'", $ffmpegPids);
                if (!empty($ffmpegPids)) {
                    Log::warning("Killing all ffmpeg processes as a last resort: " . implode(", ", $ffmpegPids));
                    foreach ($ffmpegPids as $pid) {
                        if (is_numeric($pid)) {
                            exec("kill -9 {$pid} 2>&1");
                        }
                    }
                }
            } else {
                Log::info("No new HLS segments detected - stream appears to be stopped");
            }
        } catch (\Exception $e) {
            Log::error("Error during HLS cleanup: {$e->getMessage()}");
        }
    }
} 