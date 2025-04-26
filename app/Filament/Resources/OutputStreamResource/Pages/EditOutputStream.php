<?php

namespace App\Filament\Resources\OutputStreamResource\Pages;

use App\Filament\Resources\OutputStreamResource;
use App\Models\MultiviewLayout;
use App\Services\FFmpeg\FFmpegService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditOutputStream extends EditRecord
{
    protected static string $resource = OutputStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('startStream')
                ->label('Start Stream')
                ->color('success')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status !== 'active')
                ->action(function () {
                    $record = $this->record;
                    
                    // If the stream uses a multiview layout, start it
                    if ($record->multiview_layout_id) {
                        try {
                            $ffmpegService = app(FFmpegService::class);
                            $layout = MultiviewLayout::findOrFail($record->multiview_layout_id);
                            
                            $result = $ffmpegService->startMultiview($layout, $record);
                            
                            // Ensure the status is set to active even if there was a delay in processing
                            $record->refresh();
                            if ($result) {
                                $record->update(['status' => 'active']);
                                Notification::make()
                                    ->title('Stream Started')
                                    ->body('The multiview stream has been started successfully')
                                    ->success()
                                    ->send();
                            } else {
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
                    } else {
                        Notification::make()
                            ->title('Invalid Configuration')
                            ->body('This stream does not have a multiview layout configured.')
                            ->warning()
                            ->send();
                    }
                }),
            Action::make('stopStream')
                ->label('Stop Stream')
                ->color('danger')
                ->icon('heroicon-o-stop')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'active')
                ->action(function () {
                    $record = $this->record;
                    try {
                        $ffmpegService = app(FFmpegService::class);
                        $result = $ffmpegService->stopMultiview($record);
                        
                        if ($result) {
                            Notification::make()
                                ->title('Stream Stopped')
                                ->body('The stream has been stopped successfully')
                                ->success()
                                ->send();
                        } else {
                            // Even if the result is false, we still want to ensure the status is inactive
                            // This handles cases where the process might have died but we still want to update the DB
                            $record->refresh();
                            if ($record->status === 'active') {
                                $record->update(['status' => 'inactive']);
                            }
                            
                            Notification::make()
                                ->title('Stream Status Updated')
                                ->body('The stream status has been updated, but there may have been issues with the stop process')
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error Stopping Stream')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                        
                        // Log the error for debugging
                        Log::error('Error stopping stream: ' . $e->getMessage(), [
                            'stream_id' => $record->id,
                            'exception' => $e
                        ]);
                    }
                }),
        ];
    }
}
