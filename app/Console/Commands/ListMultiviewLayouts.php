<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MultiviewLayout;

class ListMultiviewLayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:list-layouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all multiview layouts in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $layouts = MultiviewLayout::select('id', 'name', 'rows', 'columns', 'width', 'height', 'status')->get();
        
        if ($layouts->isEmpty()) {
            $this->info('No multiview layouts found in the database.');
            return;
        }
        
        $headers = ['ID', 'Name', 'Rows', 'Columns', 'Width', 'Height', 'Status'];
        $rows = [];
        
        foreach ($layouts as $layout) {
            $rows[] = [
                $layout->id,
                $layout->name,
                $layout->rows,
                $layout->columns,
                $layout->width,
                $layout->height,
                $layout->status,
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info('');
        $this->info('Layout Positions:');
        
        foreach ($layouts as $layout) {
            $this->info("Layout: {$layout->name} (ID: {$layout->id})");
            
            $positions = $layout->layoutPositions()
                ->with('inputStream:id,name')
                ->select('id', 'multiview_layout_id', 'input_stream_id', 'position_x', 'position_y', 'width', 'height')
                ->get();
                
            if ($positions->isEmpty()) {
                $this->info('  No positions defined');
                continue;
            }
            
            $posHeaders = ['ID', 'Stream ID', 'Stream Name', 'X', 'Y', 'Width', 'Height'];
            $posRows = [];
            
            foreach ($positions as $position) {
                $posRows[] = [
                    $position->id,
                    $position->input_stream_id,
                    $position->inputStream ? $position->inputStream->name : 'N/A',
                    $position->position_x,
                    $position->position_y,
                    $position->width,
                    $position->height,
                ];
            }
            
            $this->table($posHeaders, $posRows);
        }
        
        return Command::SUCCESS;
    }
} 