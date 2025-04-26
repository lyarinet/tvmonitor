<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InputStream;

class ListInputStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:list-inputs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all input streams in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $streams = InputStream::select('id', 'name', 'protocol', 'url', 'status')->get();
        
        if ($streams->isEmpty()) {
            $this->info('No input streams found in the database.');
            return;
        }
        
        $headers = ['ID', 'Name', 'Protocol', 'URL', 'Status'];
        $rows = [];
        
        foreach ($streams as $stream) {
            $rows[] = [
                $stream->id,
                $stream->name,
                $stream->protocol,
                $stream->url,
                $stream->status,
            ];
        }
        
        $this->table($headers, $rows);
        
        return Command::SUCCESS;
    }
} 