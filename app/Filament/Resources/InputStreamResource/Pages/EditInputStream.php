<?php

namespace App\Filament\Resources\InputStreamResource\Pages;

use App\Filament\Resources\InputStreamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditInputStream extends EditRecord
{
    protected static string $resource = InputStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clearErrorLogs')
                ->label('Clear Error Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear Error Logs')
                ->modalDescription('Are you sure you want to clear all error logs for this stream? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Clear Logs')
                ->action(function () {
                    $record = $this->getRecord();
                    
                    // Check if there are any error logs
                    if (empty($record->error_log)) {
                        Notification::make()
                            ->title('No error logs to clear')
                            ->info()
                            ->send();
                        return;
                    }
                    
                    // Clear the error logs
                    $record->update([
                        'error_log' => [],
                    ]);
                    
                    Notification::make()
                        ->title('Error logs cleared successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn () => !empty($this->getRecord()->error_log)),
            Actions\DeleteAction::make(),
        ];
    }
}
