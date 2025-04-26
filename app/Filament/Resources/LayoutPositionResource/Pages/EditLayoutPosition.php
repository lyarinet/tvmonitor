<?php

namespace App\Filament\Resources\LayoutPositionResource\Pages;

use App\Filament\Resources\LayoutPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLayoutPosition extends EditRecord
{
    protected static string $resource = LayoutPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
