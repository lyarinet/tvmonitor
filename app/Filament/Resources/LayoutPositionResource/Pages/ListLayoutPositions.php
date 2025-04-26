<?php

namespace App\Filament\Resources\LayoutPositionResource\Pages;

use App\Filament\Resources\LayoutPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLayoutPositions extends ListRecords
{
    protected static string $resource = LayoutPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
