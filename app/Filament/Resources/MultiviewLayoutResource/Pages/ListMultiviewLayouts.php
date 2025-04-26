<?php

namespace App\Filament\Resources\MultiviewLayoutResource\Pages;

use App\Filament\Resources\MultiviewLayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMultiviewLayouts extends ListRecords
{
    protected static string $resource = MultiviewLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
