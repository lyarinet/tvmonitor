<?php

namespace App\Filament\Resources\OutputStreamResource\Pages;

use App\Filament\Resources\OutputStreamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutputStreams extends ListRecords
{
    protected static string $resource = OutputStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
