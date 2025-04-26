<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class ActiveStreams extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    
    protected static string $view = 'filament.pages.active-streams';
    
    protected static ?string $navigationLabel = 'Active Streams';
    
    protected static ?string $title = 'Active Output Streams';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Monitoring';
    
    // Force dark theme for this page
    protected function getTheme(): ?string
    {
        return 'filament-panels::themes.dark';
    }
    
    protected function getHeaderWidgets(): array
    {
        return [];
    }
    
    protected function getFooterWidgets(): array
    {
        return [];
    }
    
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
} 