<?php

namespace App\Filament\Resources\InputStreamResource\Pages;

use App\Filament\Resources\InputStreamResource;
use App\Jobs\MonitorStreamHealth;
use App\Models\InputStream;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListInputStreams extends ListRecords
{
    protected static string $resource = InputStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('checkAllHealth')
                ->label('Check All Health')
                ->icon('heroicon-o-heart')
                ->color('warning')
                ->action(function () {
                    MonitorStreamHealth::dispatch(null, null, true);
                    
                    Notification::make()
                        ->title('Health check initiated for all streams')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Check Health of All Streams')
                ->modalDescription('This will initiate health checks for all active input streams. Continue?')
                ->modalSubmitActionLabel('Yes, Check All'),
        ];
    }
    
    protected function getActions(): array
    {
        return [
            Actions\Action::make('checkHealth')
                ->label('Check Health')
                ->icon('heroicon-o-heart')
                ->color('warning')
                ->action(function () {
                    $activeStreams = InputStream::where('status', '!=', 'inactive')->count();
                    
                    if ($activeStreams > 0) {
                        MonitorStreamHealth::dispatch(null, null, true);
                        
                        Notification::make()
                            ->title("Health check initiated for {$activeStreams} active streams")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No active streams found')
                            ->warning()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Check Health of Active Streams')
                ->modalDescription('This will initiate health checks for all active input streams. Continue?')
                ->modalSubmitActionLabel('Yes, Check Health'),
        ];
    }
}
