<?php

namespace App\Console\Commands;

use App\Jobs\MonitorStreamHealth;
use Illuminate\Console\Command;

class MonitorStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:monitor
                            {--input-id= : Monitor a specific input stream by ID}
                            {--output-id= : Monitor a specific output stream by ID}
                            {--all : Monitor all streams}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the health of streams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputId = $this->option('input-id');
        $outputId = $this->option('output-id');
        $all = $this->option('all');

        if (!$inputId && !$outputId && !$all) {
            $this->error('Please specify either --input-id, --output-id, or --all');
            return 1;
        }

        if ($all) {
            $this->info('Monitoring all streams...');
            MonitorStreamHealth::dispatch(null, null, true);
        } elseif ($inputId) {
            $this->info("Monitoring input stream {$inputId}...");
            $inputStream = \App\Models\InputStream::find($inputId);
            
            if (!$inputStream) {
                $this->error("Input stream with ID {$inputId} not found");
                return 1;
            }
            
            MonitorStreamHealth::dispatch($inputStream, null, false);
        } elseif ($outputId) {
            $this->info("Monitoring output stream {$outputId}...");
            $outputStream = \App\Models\OutputStream::find($outputId);
            
            if (!$outputStream) {
                $this->error("Output stream with ID {$outputId} not found");
                return 1;
            }
            
            MonitorStreamHealth::dispatch(null, $outputStream, false);
        }

        $this->info('Monitoring job dispatched');
        return 0;
    }
} 