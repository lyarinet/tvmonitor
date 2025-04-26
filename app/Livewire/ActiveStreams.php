<?php

namespace App\Livewire;

use App\Services\FFmpeg\FFmpegService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class ActiveStreams extends Component
{
    public $refreshInterval = 10000; // 10 seconds
    
    /**
     * Get active output streams from FFmpegService.
     */
    #[Computed]
    public function streams()
    {
        $streams = app(FFmpegService::class)->getActiveOutputStreams();
        
        // Process additional display-related information
        foreach ($streams as &$stream) {
            // Determine status display - prioritize database status over process check
            $stream['status_display'] = match ($stream['status']['database']) {
                'active' => 'ACTIVE',
                'error' => 'ERROR',
                'stopped' => 'STOPPED',
                default => $stream['status']['process_running'] ? 'RUNNING' : 'STOPPED',
            };
            
            // Determine status color classes
            if ($stream['status']['database'] === 'active') {
                $stream['status_bg_class'] = 'bg-green-900';
                $stream['status_text_class'] = 'text-green-300';
            } elseif ($stream['status']['database'] === 'error') {
                $stream['status_bg_class'] = 'bg-orange-900';
                $stream['status_text_class'] = 'text-orange-300';
            } else {
                $stream['status_bg_class'] = 'bg-red-900';
                $stream['status_text_class'] = 'text-red-300';
            }
        }
        
        return $streams;
    }
    
    /**
     * Force refresh the streams list.
     */
    public function refresh()
    {
        $this->dispatch('$refresh');
    }
    
    /**
     * Stop an output stream.
     */
    public function stopStream($streamId)
    {
        try {
            $outputStream = \App\Models\OutputStream::findOrFail($streamId);
            $result = app(FFmpegService::class)->stopMultiview($outputStream);
            
            if ($result) {
                session()->flash('message', "Stream {$outputStream->name} stopped successfully.");
            } else {
                session()->flash('error', "Failed to stop stream {$outputStream->name}.");
            }
            
            // Refresh the streams list
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            session()->flash('error', "Error: " . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.active-streams');
    }
} 