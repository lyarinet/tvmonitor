<?php

namespace App\Console\Commands;

use App\Jobs\GenerateThumbnails;
use Illuminate\Console\Command;

class GenerateStreamThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:thumbnails
                            {--input-id= : Generate thumbnail for a specific input stream by ID}
                            {--all : Generate thumbnails for all active input streams}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnails for input streams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputId = $this->option('input-id');
        $all = $this->option('all');

        if (!$inputId && !$all) {
            $this->error('Please specify either --input-id or --all');
            return 1;
        }

        if ($all) {
            $this->info('Generating thumbnails for all active input streams...');
            GenerateThumbnails::dispatch(null, true);
        } elseif ($inputId) {
            $this->info("Generating thumbnail for input stream {$inputId}...");
            $inputStream = \App\Models\InputStream::find($inputId);
            
            if (!$inputStream) {
                $this->error("Input stream with ID {$inputId} not found");
                return 1;
            }
            
            GenerateThumbnails::dispatch($inputStream, false);
        }

        $this->info('Thumbnail generation job dispatched');
        return 0;
    }
} 