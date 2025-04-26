<?php

namespace App\Filament\Resources\OutputStreamResource\Pages;

use App\Filament\Resources\OutputStreamResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\FFmpegService;
use App\Models\MultiviewLayout;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CreateOutputStream extends CreateRecord
{
    protected static string $resource = OutputStreamResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $requiresProcessing = $record->requires_processing;
        
        // If the stream uses a multiview layout, start it automatically
        if ($requiresProcessing && $record->multiview_layout_id) {
            try {
                $ffmpegService = app(FFmpegService::class);
                $layout = MultiviewLayout::findOrFail($record->multiview_layout_id);
                
                $result = $ffmpegService->startMultiview($layout, $record);
                
                // Ensure the status is set to active even if there was a delay in processing
                $record->refresh();
                if ($record->status !== 'active' && $result) {
                    $record->update(['status' => 'active']);
                    Notification::make()
                        ->title('Stream Started')
                        ->body('The multiview stream has been started successfully')
                        ->success()
                        ->send();
                } elseif (!$result) {
                    Notification::make()
                        ->title('Stream Start Failed')
                        ->body('There was an error starting the stream. Check the logs for more information.')
                        ->danger()
                        ->send();
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error Starting Stream')
                    ->body('Error: ' . $e->getMessage())
                    ->danger()
                    ->send();
                
                // Log the error for debugging
                Log::error('Error starting stream: ' . $e->getMessage(), [
                    'stream_id' => $record->id,
                    'multiview_layout_id' => $record->multiview_layout_id,
                    'exception' => $e
                ]);
            }
        }
    }
}
