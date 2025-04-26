<?php

namespace App\Console\Commands;

use App\Models\InputStream;
use App\Services\Monitoring\StreamMonitoringService;
use Illuminate\Console\Command;

class TestStreamHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:test-health {id? : The ID of the stream to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the health check for a stream';

    /**
     * Execute the console command.
     */
    public function handle(StreamMonitoringService $monitoringService)
    {
        $id = $this->argument('id');
        
        if ($id) {
            $stream = InputStream::find($id);
            
            if (!$stream) {
                $this->error("Stream with ID {$id} not found");
                return 1;
            }
            
            $this->info("Testing health check for stream: {$stream->name}");
        } else {
            // Get the most recently created UDP stream
            $stream = InputStream::where('protocol', 'udp')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$stream) {
                $this->error("No UDP streams found");
                return 1;
            }
            
            $this->info("Testing health check for the most recent UDP stream: {$stream->name}");
        }
        
        $this->info("Stream details:");
        $this->table(
            ['ID', 'Name', 'Protocol', 'URL', 'Program ID', 'Ignore Unknown', 'Map -d', 'Map -s'],
            [[
                $stream->id,
                $stream->name,
                $stream->protocol,
                $stream->url,
                $stream->program_id ?? 'N/A',
                $stream->ignore_unknown ? 'Yes' : 'No',
                $stream->map_disable_data ? 'Yes' : 'No',
                $stream->map_disable_subtitles ? 'Yes' : 'No',
            ]]
        );
        
        $this->info("Running health check...");
        $result = $monitoringService->monitorInputStream($stream);
        
        $this->info("Health check result:");
        $this->info("Status: " . $result['status']);
        
        if (isset($result['command'])) {
            $this->info("FFprobe Command used:");
            $this->line($result['command']);
        }
        
        if (isset($result['ffmpeg_command'])) {
            $this->info("FFmpeg Equivalent Command:");
            $this->line($result['ffmpeg_command']);
        }
        
        if ($result['status'] === 'error') {
            $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
        }
        
        return 0;
    }
} 