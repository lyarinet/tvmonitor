<?php

namespace App\Filament\Resources\MultiviewLayoutResource\Pages;

use App\Filament\Resources\LayoutPositionResource;
use App\Filament\Resources\MultiviewLayoutResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMultiviewLayout extends EditRecord
{
    protected static string $resource = MultiviewLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateGrid')
                ->label('Generate Grid Layout')
                ->action(function () {
                    $this->record->generateGridLayout();
                    Notification::make()
                        ->title('Grid layout generated successfully')
                        ->success()
                        ->send();
                    $this->redirect(MultiviewLayoutResource::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\Action::make('addPosition')
                ->label('Add Position')
                ->url(fn () => LayoutPositionResource::getUrl('create', [
                    'multiview_layout_id' => $this->record->id,
                ])),
            Actions\DeleteAction::make(),
        ];
    }
}
