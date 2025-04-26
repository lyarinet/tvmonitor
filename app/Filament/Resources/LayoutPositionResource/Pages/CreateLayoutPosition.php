<?php

namespace App\Filament\Resources\LayoutPositionResource\Pages;

use App\Filament\Resources\LayoutPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateLayoutPosition extends CreateRecord
{
    protected static string $resource = LayoutPositionResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure multiview_layout_id is set
        if (empty($data['multiview_layout_id']) && request()->has('multiview_layout_id')) {
            $data['multiview_layout_id'] = request()->input('multiview_layout_id');
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
