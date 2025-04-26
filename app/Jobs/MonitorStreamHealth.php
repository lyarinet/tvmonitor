<?php

namespace App\Jobs;

use App\Models\InputStream;
use App\Models\OutputStream;
use App\Services\Monitoring\StreamMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorStreamHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?InputStream $inputStream = null;
    protected ?OutputStream $outputStream = null;
    protected bool $monitorAll;

    /**
     * Create a new job instance.
     */
    public function __construct(?InputStream $inputStream = null, ?OutputStream $outputStream = null, bool $monitorAll = false)
    {
        $this->inputStream = $inputStream;
        $this->outputStream = $outputStream;
        $this->monitorAll = $monitorAll;
    }

    /**
     * Execute the job.
     */
    public function handle(StreamMonitoringService $monitoringService): void
    {
        try {
            if ($this->monitorAll) {
                Log::info("Monitoring all streams");
                
                $inputResults = $monitoringService->monitorInputStreams();
                $outputResults = $monitoringService->monitorOutputStreams();
                
                Log::info("Monitored " . count($inputResults) . " input streams and " . count($outputResults) . " output streams");
            } elseif ($this->inputStream) {
                Log::info("Monitoring input stream {$this->inputStream->id}");
                
                $result = $monitoringService->monitorInputStream($this->inputStream);
                
                Log::info("Input stream {$this->inputStream->id} status: {$result['status']}");
            } elseif ($this->outputStream) {
                Log::info("Monitoring output stream {$this->outputStream->id}");
                
                $result = $monitoringService->monitorOutputStream($this->outputStream);
                
                Log::info("Output stream {$this->outputStream->id} status: {$result['status']}");
            } else {
                Log::warning("No streams specified for monitoring");
            }
        } catch (\Exception $e) {
            Log::error("Error monitoring stream health: {$e->getMessage()}");
            
            // Fail the job
            $this->fail($e);
        }
    }
} 