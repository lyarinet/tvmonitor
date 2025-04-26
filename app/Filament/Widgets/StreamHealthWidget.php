<?php

namespace App\Filament\Widgets;

use App\Models\InputStream;
use App\Models\OutputStream;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class StreamHealthWidget extends ChartWidget
{
    protected static ?string $heading = 'Stream Health';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        // Get counts of streams by status
        $inputStreamCounts = [
            'active' => InputStream::where('status', 'active')->count(),
            'inactive' => InputStream::where('status', 'inactive')->count(),
            'error' => InputStream::where('status', 'error')->count(),
        ];

        $outputStreamCounts = [
            'active' => OutputStream::where('status', 'active')->count(),
            'inactive' => OutputStream::where('status', 'inactive')->count(),
            'error' => OutputStream::where('status', 'error')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Input Streams',
                    'data' => array_values($inputStreamCounts),
                    'backgroundColor' => ['#10b981', '#94a3b8', '#ef4444'],
                ],
                [
                    'label' => 'Output Streams',
                    'data' => array_values($outputStreamCounts),
                    'backgroundColor' => ['#10b981', '#94a3b8', '#ef4444'],
                ],
            ],
            'labels' => ['Active', 'Inactive', 'Error'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
