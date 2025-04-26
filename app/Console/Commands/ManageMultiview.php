<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMultiviewStream;
use App\Models\MultiviewLayout;
use App\Models\OutputStream;
use Illuminate\Console\Command;

class ManageMultiview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multiview:manage
                            {action : The action to perform (start, stop)}
                            {--output-id= : The output stream ID}
                            {--layout-id= : The multiview layout ID (required for start action)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage multiview processes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $outputId = $this->option('output-id');
        $layoutId = $this->option('layout-id');

        if (!in_array($action, ['start', 'stop'])) {
            $this->error('Invalid action. Use "start" or "stop"');
            return 1;
        }

        if (!$outputId) {
            $this->error('Please specify --output-id');
            return 1;
        }

        $outputStream = OutputStream::find($outputId);
        if (!$outputStream) {
            $this->error("Output stream with ID {$outputId} not found");
            return 1;
        }

        if ($action === 'start') {
            if (!$layoutId) {
                $this->error('Please specify --layout-id for start action');
                return 1;
            }

            $layout = MultiviewLayout::find($layoutId);
            if (!$layout) {
                $this->error("Multiview layout with ID {$layoutId} not found");
                return 1;
            }

            $this->info("Starting multiview process for layout {$layoutId} and output stream {$outputId}...");
            ProcessMultiviewStream::dispatch($layout, $outputStream, true);
        } else {
            $this->info("Stopping multiview process for output stream {$outputId}...");
            ProcessMultiviewStream::dispatch($outputStream->multiviewLayout, $outputStream, false);
        }

        $this->info('Multiview management job dispatched');
        return 0;
    }
} 