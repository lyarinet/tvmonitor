<?php

namespace App\Jobs;

use App\Models\InputStream;
use App\Services\Monitoring\StreamMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateThumbnails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?InputStream $inputStream = null;
    protected bool $generateAll;
    
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(?InputStream $inputStream = null, bool $generateAll = false)
    {
        $this->inputStream = $inputStream;
        $this->generateAll = $generateAll;
        $this->onQueue('thumbnails');
    }

    /**
     * Execute the job.
     */
    public function handle(StreamMonitoringService $monitoringService): void
    {
        try {
            if ($this->generateAll) {
                Log::info("Generating thumbnails for all active input streams");
                
                $results = $monitoringService->generateThumbnails();
                
                $successCount = count(array_filter($results, fn($result) => $result !== null));
                $failCount = count($results) - $successCount;
                
                Log::info("Generated {$successCount} thumbnails successfully, {$failCount} failed");
            } elseif ($this->inputStream) {
                Log::info("Generating thumbnail for input stream {$this->inputStream->id}");
                
                $result = $monitoringService->generateThumbnail($this->inputStream);
                
                if ($result) {
                    Log::info("Successfully generated thumbnail for input stream {$this->inputStream->id}");
                } else {
                    Log::error("Failed to generate thumbnail for input stream {$this->inputStream->id}");
                }
            } else {
                Log::warning("No input stream specified for thumbnail generation");
            }
        } catch (\Exception $e) {
            Log::error("Error generating thumbnails: {$e->getMessage()}");
            
            // Fail the job
            $this->fail($e);
        }
    }
} 