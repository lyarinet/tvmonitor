<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OutputStream;

class ListOutputStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:list-outputs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all output streams in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $streams = OutputStream::select('id', 'name', 'protocol', 'url', 'multiview_layout_id', 'status')->get();
        
        if ($streams->isEmpty()) {
            $this->info('No output streams found in the database.');
            return;
        }
        
        $headers = ['ID', 'Name', 'Protocol', 'URL', 'Layout ID', 'Status'];
        $rows = [];
        
        foreach ($streams as $stream) {
            $rows[] = [
                $stream->id,
                $stream->name,
                $stream->protocol,
                $stream->url,
                $stream->multiview_layout_id,
                $stream->status,
            ];
        }
        
        $this->table($headers, $rows);
        
        return Command::SUCCESS;
    }
} 