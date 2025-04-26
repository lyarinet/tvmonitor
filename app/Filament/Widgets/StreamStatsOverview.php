<?php

namespace App\Filament\Widgets;

use App\Models\InputStream;
use App\Models\MultiviewLayout;
use App\Models\OutputStream;
use App\Services\FFmpeg\FFmpegService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StreamStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $ffmpegService = app(FFmpegService::class);
        $ffmpegInstalled = $ffmpegService->checkFFmpegInstallation();
        $ffmpegVersion = $ffmpegService->getFFmpegVersion();

        return [
            Stat::make('Input Streams', InputStream::count())
                ->description('Total input streams')
                ->descriptionIcon('heroicon-m-arrow-down-on-square')
                ->color('primary'),
            
            Stat::make('Output Streams', OutputStream::count())
                ->description('Total output streams')
                ->descriptionIcon('heroicon-m-arrow-up-on-square')
                ->color('warning'),
            
            Stat::make('Multiview Layouts', MultiviewLayout::count())
                ->description('Total multiview layouts')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('success'),
            
            Stat::make('Active Streams', InputStream::where('status', 'active')->count() . ' in / ' . OutputStream::where('status', 'active')->count() . ' out')
                ->description('Currently active streams')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
            
            Stat::make('FFmpeg Status', $ffmpegInstalled ? 'Installed' : 'Not Installed')
                ->description($ffmpegVersion ?? 'Unknown version')
                ->descriptionIcon($ffmpegInstalled ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($ffmpegInstalled ? 'success' : 'danger'),
        ];
    }
}
