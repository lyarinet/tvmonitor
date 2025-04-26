<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Channel Monitoring - Available Streams</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #333;
        }
        
        header h1 {
            margin: 0;
            color: #fff;
            font-size: 24px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            color: #fff;
            font-size: 20px;
            margin-bottom: 20px;
            border-left: 3px solid #ff8c00;
            padding-left: 15px;
            display: flex;
            align-items: center;
        }
        
        .streams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .stream-card {
            background-color: #1a1a1a;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .stream-thumb {
            height: 200px;
            background-color: #000;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stream-thumb.preview {
            background-color: #1a1a1a;
            display: grid;
            padding: 20px;
            gap: 4px;
            height: 200px;
            position: relative;
        }

        .preview-cell {
            background-color: #2d2d2d;
            border-radius: 2px;
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .preview-cell.active {
            background-color: #000;
        }

        .preview-cell video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-cell img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .stream-resolution {
            position: absolute;
            bottom: 10px;
            left: 10px;
            font-size: 12px;
            color: #aaa;
        }

        .stream-bitrate {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 12px;
            color: #aaa;
        }
        
        .live-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #fff;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #4caf50;
            border-radius: 50%;
        }

        .preview-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #1d1d1d;
            padding: 4px 8px;
            border-radius: 2px;
            font-size: 12px;
            color: #666;
            font-weight: 500;
            z-index: 2;
        }

        .stream-status {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 10px;
            color: #4caf50;
            z-index: 2;
        }
        
        .stream-info {
            padding: 15px;
        }
        
        .stream-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #fff;
        }
        
        .stream-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .meta-item {
            color: #aaa;
        }
        
        .meta-label {
            color: #666;
            margin-right: 5px;
        }
        
        .stream-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            color: #fff;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-watch {
            background-color: #2196f3;
        }
        
        .btn-start {
            background-color: #4caf50;
        }
        
        .btn-stop {
            background-color: #f44336;
        }
        
        .btn-settings {
            background-color: #333;
        }
        
        .btn-logs {
            background-color: #ff8c00;
        }

        .btn-view {
            background-color: #333;
        }

        .btn-edit {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>TV Channel Monitoring</h1>
        </header>
 
        
        <div class="section">
            <h2 class="section-title">Multiview Layouts</h2>
            @if(count($layouts) > 0)
                <div class="streams-grid">
                    @foreach($layouts as $layout)
                        <div class="stream-card">
                            <div class="stream-thumb preview" style="grid-template-columns: repeat({{ $layout->name == '1x1' ? 1 : ($layout->name == '6x6' ? 6 : 3) }}, 1fr);">
                                @php
                                    $positions = $layout->layoutPositions;
                                    $totalCells = $layout->name == '1x1' ? 1 : ($layout->name == '6x6' ? 36 : 9);
                                @endphp
                                
                                @for($i = 0; $i < $totalCells; $i++)
                                    @php
                                        $position = $positions->where('position', $i + 1)->first();
                                        $stream = $position->stream ?? null;
                                    @endphp
                                    <div class="preview-cell {{ $stream ? 'active' : '' }}">
                                        @if($stream && $stream->is_active)
                                            @if($stream->thumbnail_url)
                                                <img src="{{ $stream->thumbnail_url }}" alt="Stream {{ $i + 1 }}">
                                            @elseif($stream->stream_url)
                                                <video autoplay muted playsinline>
                                                    <source src="{{ $stream->stream_url }}" type="application/x-mpegURL">
                                                </video>
                                            @endif
                                            <div class="stream-status">LIVE</div>
                                        @endif
                                    </div>
                                @endfor
                                <div class="preview-label">PREVIEW</div>
                            </div>
                            <div class="stream-info">
                                <div class="stream-name">{{ $layout->name }}</div>
                                <div class="stream-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Size:</span>
                                        {{ $layout->width ?? '1920' }}x{{ $layout->height ?? '1080' }}
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Positions:</span>
                                        {{ $totalCells }}
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Active:</span>
                                        {{ $positions->whereNotNull('stream_id')->count() }}
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Type:</span>
                                        Standard
                                    </div>
                                </div>
                                <div class="stream-actions">
                                    <a href="{{ route('view.multiview', $layout->id) }}" class="btn btn-view">View Layout</a>
                                    <a href="{{ route('filament.admin.resources.multiview-layouts.edit', ['record' => $layout->id]) }}" class="btn btn-edit">Edit</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>No multiview layouts available.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html> 