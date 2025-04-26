<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css\Css;
use Filament\Support\Assets\Js\Js;
use Illuminate\Support\Facades\Route;

class Guides extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.guides';
    
    protected static ?string $navigationLabel = 'User Guides';
    
    protected static ?string $title = 'User Guides';
    
    protected static ?int $navigationSort = 90;
    
    protected static ?string $navigationGroup = 'Help & Support';
    
    public function mount(): void
    {
        // Redirect to the guides index page
        redirect()->route('guides.index');
    }
} 