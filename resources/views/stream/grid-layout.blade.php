<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $layout->name }} - TV Channel Monitoring</title>
    
    <!-- Include HLS.js for video playback -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    
    <script>
        // Debug info to help diagnose issues
        console.log('Layout data:', {
            id: {{ $layout->id }},
            name: "{{ $layout->name }}",
            width: {{ $layoutWidth }},
            height: {{ $layoutHeight }},
            streamCount: {{ count($streamData) }},
            isPreview: {{ $isPreview ? 'true' : 'false' }}
        });
        
        console.log('Stream data:', @json($streamData));
    </script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        .header {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .dashboard-link {
            padding: 8px 15px;
            background-color: #9b59b6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .dashboard-link:hover {
            background-color: #8e44ad;
        }

        .dashboard-link svg {
            width: 16px;
            height: 16px;
        }
        
        .multiview-container {
            position: relative;
            width: 100vw;
            height: calc(100vh - 60px); /* Adjust for header height */
            overflow: hidden;
            background-color: {{ $layout->background_color ?? '#000000' }};
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stream-container {
            position: absolute;
            overflow: hidden;
            background-color: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #333;
            box-sizing: border-box;
        }
        
        .stream-active {
            border-color: #4caf50;
        }
        
        .stream-inactive {
            border-color: #555;
        }
        
        /* Responsive calculations for grid layout */
        @media (min-width: 1200px) {
            .multiview-container {
                max-width: 1920px;
                max-height: 1080px;
                margin: 0 auto;
            }
        }
        
        .stream-label {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            font-size: 16px;
            z-index: 10;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stream-status-indicator {
            display: inline-block;
            font-size: 12px;
            width: 12px;
            height: 12px;
            line-height: 12px;
            text-align: center;
        }
        
        .stream-status-indicator.active {
            color: #4caf50;
        }
        
        .stream-status-indicator.inactive {
            color: #aaa;
        }
        
        .label-bottom {
            bottom: 10px;
            left: 10px;
        }
        
        .label-top {
            top: 10px;
            left: 10px;
        }
        
        .label-left {
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
        }
        
        .label-right {
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
        }
        
        .stream-placeholder {
            font-size: 18px;
            color: #FFFFFF;
            text-align: center;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background-color: #000;
        }
        
        .back-button {
            padding: 8px 15px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background-color: #555;
        }
        
        .status-indicator {
            background-color: #333;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .status-live {
            color: #4caf50;
        }
        
        .status-preview {
            color: #ff9800;
        }
        
        #preview-multiview {
            width: 100%;
            display: grid;
            grid-gap: 2px;
            padding: 0;
            background-color: #222;
            overflow: hidden;
        }

        .video-container {
            position: relative;
            height: 100%;
            width: 100%;
            background-color: #000;
            overflow: hidden;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .stream-title {
            position: absolute;
            top: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 5px;
            font-size: 12px;
            z-index: 10;
        }

        .error-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            z-index: 20;
            padding: 20px;
            box-sizing: border-box;
        }

        .error-overlay p {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .retry-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: background-color 0.2s;
        }

        .retry-button:hover {
            background-color: #2980b9;
        }
        
        /* Status dialog and button */
        .status-check-button {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .status-check-button:hover {
            background-color: #2980b9;
        }
        
        .status-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000; /* Ensure it's above everything else */
            align-items: center;
            justify-content: center;
        }
        
        /* When status dialog is shown */
        .status-dialog.show {
            display: flex;
        }
        
        .status-dialog-content {
            background-color: #222;
            border-radius: 5px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow: auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        
        .status-dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #333;
        }
        
        .status-dialog-header h3 {
            margin: 0;
            color: white;
        }
        
        .close-dialog-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .status-dialog-body {
            padding: 20px;
            color: white;
        }
        
        .status-results {
            margin-top: 15px;
            padding: 10px;
            background-color: #333;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 50vh;
            overflow: auto;
        }

        .motion-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            z-index: 10;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        
        .motion-active {
            background-color: rgba(255, 36, 0, 0.7);
            transform: scale(1.2);
        }
        
        .motion-debug {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 5;
            pointer-events: none;
            opacity: 0;
        }
        
        /* Volume level indicator */
        .volume-indicator {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 140px;
            height: 10px;
            background-color: #000000;
            border-radius: 2px;
            overflow: hidden;
            z-index: 30;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.8), inset 0 0 2px rgba(0, 0, 0, 0.8);
            opacity: 0.85;
            border: 1px solid rgba(50, 50, 50, 0.5);
        }
        
        .volume-level {
            height: 100%;
            width: 30%;
            background: linear-gradient(to right, 
                #00ff00 0%, 
                #00ff00 65%, 
                #ffff00 65%, 
                #ffff00 85%, 
                #ff0000 85%, 
                #ff0000 100%);
            transition: width 0.05s ease;
        }
        
        .volume-peak {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 2px;
            background-color: #FFFFFF;
            z-index: 1;
            opacity: 1;
            transition: opacity 0.5s ease-out;
            box-shadow: 0 0 3px rgba(255, 255, 255, 0.8);
        }
        
        /* Add tick marks to volume indicator */
        .volume-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(to right,
                transparent 0%,
                transparent 24%,
                rgba(255, 255, 255, 0.3) 24%,
                rgba(255, 255, 255, 0.3) 25%,
                transparent 25%,
                transparent 49%,
                rgba(255, 255, 255, 0.3) 49%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 50%,
                transparent 74%,
                rgba(255, 255, 255, 0.3) 74%,
                rgba(255, 255, 255, 0.3) 75%,
                transparent 75%,
                transparent 100%);
            pointer-events: none;
            z-index: 2;
        }
        
        /* Performance Toggle */
        .performance-toggle {
            position: absolute;
            bottom: 5px;
            left: 15px;
            z-index: 20;
            font-size: 12px;
            display: flex;
            align-items: center;
            color: #fff;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .performance-toggle:hover {
            opacity: 1;
        }
        
        .toggle-checkbox {
            appearance: none;
            width: 32px;
            height: 16px;
            background: #444;
            border-radius: 10px;
            position: relative;
            margin: 0 8px;
            cursor: pointer;
        }
        
        .toggle-checkbox:checked {
            background: #2196F3;
        }
        
        .toggle-checkbox:before {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            background: #fff;
            transition: 0.2s;
        }
        
        .toggle-checkbox:checked:before {
            left: 18px;
        }

        /* Grid styles for different layouts */
        .volume-labels {
            position: absolute;
            top: -14px;
            left: 0;
            width: 100%;
            height: 12px;
            font-size: 8px;
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: space-between;
            padding: 0 5px;
            box-sizing: border-box;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .volume-label-min {
            text-align: left;
        }
        
        .volume-label-mid {
            text-align: center;
        }
        
        .volume-label-max {
            text-align: right;
        }

        /* Add LIVE indicator styles */
        .live-indicator {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 8px;
            vertical-align: middle;
            animation: live-pulse 2s infinite;
            letter-spacing: 0.5px;
        }
        
        @keyframes live-pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        
        .stream-header {
            position: absolute;
            top: 10px;
            left: 10px;
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
            z-index: 25;
            display: flex;
            align-items: center;
        }

        /* Audio level meter on left side */
        .audio-level-meter {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 120px;
            background-color: #111;
            border-radius: 4px;
            overflow: hidden;
            z-index: 20;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column-reverse;
        }
        
        .audio-level-fill {
            width: 100%;
            height: 30%;
            background: linear-gradient(to top, 
                #00ff00 0%, 
                #00ff00 65%, 
                #ffff00 65%, 
                #ffff00 85%, 
                #ff0000 85%, 
                #ff0000 100%);
            transition: height 0.05s ease;
        }
        
        /* Audio level meter ticks */
        .audio-level-meter::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(to bottom,
                transparent 0%,
                transparent 14%,
                rgba(255, 255, 255, 0.3) 14%,
                rgba(255, 255, 255, 0.3) 15%,
                transparent 15%,
                transparent 39%,
                rgba(255, 255, 255, 0.3) 39%,
                rgba(255, 255, 255, 0.3) 40%,
                transparent 40%,
                transparent 64%,
                rgba(255, 255, 255, 0.3) 64%,
                rgba(255, 255, 255, 0.3) 65%,
                transparent 65%,
                transparent 84%,
                rgba(255, 255, 255, 0.3) 84%,
                rgba(255, 255, 255, 0.3) 85%,
                transparent 85%,
                transparent 100%);
            pointer-events: none;
        }

        /* Fullscreen button and styles */
        #fullscreen-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        #fullscreen-btn:hover {
            background-color: #2c3e50;
        }

        /* Fullscreen mode styles */
        .multiview-container:fullscreen {
            background-color: black;
            width: 100vw;
            height: 100vh;
            padding: 0;
            margin: 0;
        }

        .multiview-container:fullscreen .status-check-button,
        .multiview-container:fullscreen .back-button,
        .multiview-container:fullscreen .performance-toggle {
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .multiview-container:fullscreen .status-check-button:hover,
        .multiview-container:fullscreen .back-button:hover,
        .multiview-container:fullscreen .performance-toggle:hover {
            opacity: 1;
        }

        .multiview-container:fullscreen .header {
            background-color: rgba(0, 0, 0, 0.5);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            transition: opacity 0.3s;
            opacity: 0.8;
        }

        .multiview-container:fullscreen .header:hover {
            opacity: 1;
        }

        /* Vendor prefixed fullscreen CSS */
        .multiview-container:-webkit-full-screen { background-color: black; }
        .multiview-container:-moz-full-screen { background-color: black; }
        .multiview-container:-ms-fullscreen { background-color: black; }

        /* Handle floating controls better in fullscreen */
        :fullscreen .stream-container .stream-stats-btn,
        :fullscreen .stream-container .stream-label {
            opacity: 0;
            transition: opacity 0.3s;
        }

        :fullscreen .stream-container:hover .stream-stats-btn,
        :fullscreen .stream-container:hover .stream-label {
            opacity: 1;
        }

        /* Vendor prefixed selectors */
        :-webkit-full-screen .stream-container .stream-stats-btn,
        :-webkit-full-screen .stream-container .stream-label { opacity: 0; }
        :-webkit-full-screen .stream-container:hover .stream-stats-btn,
        :-webkit-full-screen .stream-container:hover .stream-label { opacity: 1; }

        :-moz-full-screen .stream-container .stream-stats-btn,
        :-moz-full-screen .stream-container .stream-label { opacity: 0; }
        :-moz-full-screen .stream-container:hover .stream-stats-btn,
        :-moz-full-screen .stream-container:hover .stream-label { opacity: 1; }

        /* Floating Controls Panel */
        .floating-controls {
            position: fixed;
            top: 140px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 4px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 1000;
            width: 200px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .live-badge {
            background-color: #ff0000;
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 6px 0;
            border-radius: 4px;
            width: 100%;
            margin-bottom: 4px;
            letter-spacing: 1px;
            font-size: 14px;
        }
        
        .control-button {
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        
        .control-button:hover {
            opacity: 0.9;
        }
        
        .check-status-btn {
            background-color: #3498db;
        }
        
        .fullscreen-btn {
            background-color: #2ecc71;
        }
        
        .back-btn {
            background-color: #7f8c8d;
        }
        
        /* Hide button text on smaller screens */
        @media (max-width: 768px) {
            .control-button span {
                display: none;
            }
        }
        
        /* Better positioning in fullscreen mode */
        :fullscreen .floating-controls {
            z-index: 9999;
        }
        
        /* Fix for retry button on error */
        .retry-button-wrapper {
            text-align: center;
            margin-top: 10px;
        }
        
        .retry-with-cache {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .retry-with-cache:hover {
            background-color: #c0392b;
        }

        /* Grid Header with Horizontal Controls */
        .grid-header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            font-size: 24px;
            border-bottom: 1px solid #333;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .grid-header h1 {
            margin: 0;
            font-weight: bold;
            font-size: 24px;
            font-family: Arial, sans-serif;
        }
        
        .horizontal-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .live-badge-horizontal {
            background-color: #ff0000;
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px;
            letter-spacing: 1px;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .control-button-horizontal {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        
        .control-button-horizontal:hover {
            opacity: 0.9;
        }
        
        .check-status-btn-horizontal {
            background-color: #3498db;
        }
        
        .fullscreen-btn-horizontal {
            background-color: #2ecc71;
        }
        
        .back-btn-horizontal {
            background-color: #7f8c8d;
        }
        
        /* Hide floating and sidebar controls - using horizontal controls now */
        .floating-controls, .sidebar-controls {
            display: none;
        }
        
        /* Responsive adjustments for small screens */
        @media (max-width: 768px) {
            .grid-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .horizontal-controls {
                width: 100%;
                flex-wrap: wrap;
            }
        }

        /* Add these styles for the performance toggle switch */
        .performance-toggle-horizontal {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 10px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #555;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        .slider.round {
            border-radius: 24px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #2ecc71;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .toggle-label {
            color: white;
            font-size: 14px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .toggle-label {
                display: none;
            }
        }

        /* Add these styles for the audio toggle button */
        .stream-container {
            position: absolute;
            overflow: hidden;
            background-color: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #333;
            box-sizing: border-box;
        }

        .audio-toggle {
            position: absolute;
            bottom: 10px;
            right: 10px; /* Changed from left: 10px to right: 10px */
            background-color: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 30;
        }

        .stream-container:hover .audio-toggle {
            opacity: 1;
        }

        .audio-toggle svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .audio-toggle.muted svg path {
            d: path('M11 5L6 9H2v6h4l5 4V5zM17 9.82v4.36c.84-.3 1.5-1.07 1.5-2.18S17.84 10.12 17 9.82z');
        }

        .audio-toggle:not(.muted) svg path {
            d: path('M11 5L6 9H2v6h4l5 4V5zm6.93 4.93c1.88 1.88 1.88 4.92 0 6.8-1.88 1.88-4.92 1.88-6.8 0-1.88-1.88-1.88-4.92 0-6.8 1.88-1.88 4.92-1.88 6.8 0z');
        }
    </style>
</head>
<body>
    <!-- Grid Header with Horizontal Controls -->
    <div class="grid-header">
        <h1>{{ $layout->name }}</h1>
        <div class="horizontal-controls">
            <span class="live-badge-horizontal">{{ $isPreview ? 'PREVIEW' : 'LIVE' }}</span>
            <button id="check-status-horizontal" class="control-button-horizontal check-status-btn-horizontal" onclick="showStatusDialogDirect()">Check Stream Status</button>
            <button id="fullscreen-horizontal" class="control-button-horizontal fullscreen-btn-horizontal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18-3v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3m8-2h3a2 2 0 0 0 2-2v-3"></path>
                </svg>
                Fullscreen
            </button>
            <div class="performance-toggle-horizontal">
                <label class="switch">
                    <input type="checkbox" id="performance-mode" checked>
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label">Visual Effects</span>
            </div>
            @auth
                @can('view_dashboard')
                    <a href="{{ route('view.multiview.dashboard', ['id' => $layout->id]) }}" class="dashboard-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                            <line x1="9" y1="21" x2="9" y2="9"/>
                        </svg>
                        Dashboard
                    </a>
                @endcan
            @endauth
            <a href="{{ route('view.streams') }}" class="control-button-horizontal back-btn-horizontal">Back to Streams</a>
        </div>
    </div>
    
    <!-- Status dialog -->
    <div id="status-dialog" class="status-dialog">
        <div class="status-dialog-content">
            <div class="status-dialog-header">
                <h3>Stream Status</h3>
                <button id="close-status-dialog" class="close-dialog-btn">&times;</button>
            </div>
            <div class="status-dialog-body">
                <p>Checking stream status...</p>
                <div id="status-results" class="status-results"></div>
            </div>
        </div>
    </div>
    
    <div class="multiview-container" id="multiview-container">
        @php
            // Use the layout dimensions provided by the controller
            $containerWidth = $layoutWidth;
            $containerHeight = $layoutHeight;
            
            // Adjust container style
            $containerStyle = "position: relative; width: 100%; height: 100%; max-width: {$containerWidth}px; max-height: {$containerHeight}px; margin: 0 auto;";
        @endphp
        
        <div style="{{ $containerStyle }}">
            @foreach($streamData as $index => $stream)
                @php
                    // Calculate percentage positions and dimensions for responsiveness
                    $posX = ($stream['position_x'] / $containerWidth) * 100;
                    $posY = ($stream['position_y'] / $containerHeight) * 100;
                    $width = ($stream['width'] / $containerWidth) * 100;
                    $height = ($stream['height'] / $containerHeight) * 100;
                    
                    // Determine stream status class
                    $statusClass = $stream['is_active'] ?? false ? 'stream-active' : 'stream-inactive';
                @endphp
                
                <div class="stream-container {{ $statusClass }}" id="stream-{{ $index }}" style="
                    left: {{ $posX }}%;
                    top: {{ $posY }}%;
                    width: {{ $width }}%;
                    height: {{ $height }}%;
                    background-color: {{ $layout->background_color ?? '#000000' }};
                ">
                    @if($stream['show_label'] && $stream['input_name'])
                        <div class="stream-label label-{{ $stream['label_position'] ?? 'bottom' }}">
                            {{ $stream['input_name'] }}
                            @if(isset($stream['is_active']))
                                <span class="stream-status-indicator {{ $stream['is_active'] ? 'active' : 'inactive' }}">
                                    ●
                                </span>
                            @endif
                        </div>
                    @endif
                    
                    @if($stream['has_stream'])
                        <video id="video-{{ $index }}" autoplay muted playsinline></video>
                        <button class="audio-toggle muted" onclick="toggleAudio('{{ $index }}')">
                            <svg viewBox="0 0 24 24">
                                <path d="M11 5L6 9H2v6h4l5 4V5zM17 9.82v4.36c.84-.3 1.5-1.07 1.5-2.18S17.84 10.12 17 9.82z"/>
                            </svg>
                        </button>
                        <div class="audio-level-meter">
                            <div id="audio-level-fill-{{ $index }}" class="audio-level-fill"></div>
                        </div>
                        <div id="motion-indicator-{{ $index }}" class="motion-indicator"></div>
                        <canvas id="motion-debug-{{ $index }}" class="motion-debug"></canvas>
                        <div id="error-{{ $index }}" class="error-overlay">
                            <p>Stream could not be loaded</p>
                            <button class="retry-button" onclick="retryStream('{{ $index }}', '{{ $stream['stream_url'] }}')">Retry</button>
                        </div>
                    @else
                        <div class="stream-placeholder">{{ $stream['input_name'] ?? 'No stream' }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize each stream with HLS.js
            @foreach($streamData as $index => $stream)
                @if($stream['has_stream'] && $stream['stream_url'])
                    console.log('Setting up stream {{ $index }}:', {
                        url: '{{ $stream['stream_url'] }}',
                        name: '{{ $stream['input_name'] ?? "Stream " . $index }}',
                        active: {{ isset($stream['is_active']) && $stream['is_active'] ? 'true' : 'false' }}
                    });
                    initializeStream('{{ $index }}', '{{ $stream['stream_url'] }}');
                @endif
            @endforeach
            
            // Setup fullscreen button functionality
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function() {
                    toggleFullscreen();
                });
            }
            
            // Setup horizontal fullscreen button
            const fullscreenHorizontalBtn = document.getElementById('fullscreen-horizontal');
            if (fullscreenHorizontalBtn) {
                console.log('Horizontal fullscreen button found');
                fullscreenHorizontalBtn.addEventListener('click', function() {
                    console.log('Horizontal fullscreen button clicked');
                    toggleFullscreen();
                });
            } else {
                console.warn('Horizontal fullscreen button not found');
            }
            
            // Setup check status button
            const checkStatusHorizontalBtn = document.getElementById('check-status-horizontal');
            if (checkStatusHorizontalBtn) {
                console.log('Check status horizontal button found');
                checkStatusHorizontalBtn.addEventListener('click', function() {
                    console.log('Check status button clicked');
                    showStatusDialog();
                });
            } else {
                console.warn('Check status horizontal button not found');
            }
            
            // Function to toggle fullscreen mode
            function toggleFullscreen() {
                const multiviewContainer = document.getElementById('multiview-container');
                
                if (!document.fullscreenElement &&    // alternative standard method
                    !document.mozFullScreenElement && 
                    !document.webkitFullscreenElement && 
                    !document.msFullscreenElement) {
                    
                    // Enter fullscreen
                    if (multiviewContainer.requestFullscreen) {
                        multiviewContainer.requestFullscreen();
                    } else if (multiviewContainer.msRequestFullscreen) {
                        multiviewContainer.msRequestFullscreen();
                    } else if (multiviewContainer.mozRequestFullScreen) {
                        multiviewContainer.mozRequestFullScreen();
                    } else if (multiviewContainer.webkitRequestFullscreen) {
                        multiviewContainer.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                    
                    // Update button text
                    if (fullscreenBtn) {
                        fullscreenBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: text-bottom;">
                                <path d="M4 14h6m-6 4v-4m16-4h-6m6-4v4"/>
                            </svg>
                            Exit Fullscreen
                        `;
                    }
                } else {
                    // Exit fullscreen
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                    
                    // Update button text
                    if (fullscreenBtn) {
                        fullscreenBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: text-bottom;">
                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18-3v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3m8-2h3a2 2 0 0 0 2-2v-3"></path>
                            </svg>
                            Fullscreen
                        `;
                    }
                }
            }
            
            // Listen for fullscreen change events to update button state
            document.addEventListener('fullscreenchange', updateFullscreenButtonState);
            document.addEventListener('webkitfullscreenchange', updateFullscreenButtonState);
            document.addEventListener('mozfullscreenchange', updateFullscreenButtonState);
            document.addEventListener('MSFullscreenChange', updateFullscreenButtonState);
            
            function updateFullscreenButtonState() {
                if (!fullscreenBtn) return;
                
                const isFullscreen = document.fullscreenElement || 
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement;
                
                if (isFullscreen) {
                    // We're in fullscreen mode
                    fullscreenBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: text-bottom;">
                            <path d="M4 14h6m-6 4v-4m16-4h-6m6-4v4"/>
                        </svg>
                        Exit Fullscreen
                    `;
                    
                    // Also update horizontal button if it exists
                    const fullscreenHorizontalBtn = document.getElementById('fullscreen-horizontal');
                    if (fullscreenHorizontalBtn) {
                        fullscreenHorizontalBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 14h6m-6 4v-4m16-4h-6m6-4v4"/>
                            </svg>
                            Exit Fullscreen
                        `;
                    }
                } else {
                    // We're not in fullscreen mode
                    fullscreenBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: text-bottom;">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18-3v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3m8-2h3a2 2 0 0 0 2-2v-3"></path>
                        </svg>
                        Fullscreen
                    `;
                    
                    // Also update horizontal button if it exists
                    const fullscreenHorizontalBtn = document.getElementById('fullscreen-horizontal');
                    if (fullscreenHorizontalBtn) {
                        fullscreenHorizontalBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18-3v3a2 2 0 0 1-2 2h-3M3 16v3a2 2 0 0 0 2 2h3m8-2h3a2 2 0 0 0 2-2v-3"></path>
                            </svg>
                            Fullscreen
                        `;
                    }
                }
            }
            
            // Global retry function that can be called from HTML
            window.retryStream = function(index, playlistUrl) {
                console.log('Retrying stream ' + index);
                const errorElement = document.getElementById('error-' + index);
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
                initializeStream(index, playlistUrl);
            };
            
            function initializeStream(index, playlistUrl) {
                const video = document.getElementById('video-' + index);
                const errorElement = document.getElementById('error-' + index);
                if (!video) return;
                
                console.log('Initializing stream ' + index + ' with URL: ' + playlistUrl);
                
                // Function to show error overlay
                function showError(message) {
                    console.error('Stream ' + index + ' error:', message);
                    if (errorElement) {
                        const errorText = errorElement.querySelector('p');
                        if (errorText) errorText.textContent = message || 'Stream could not be loaded';
                        errorElement.style.display = 'flex';
                    }
                }
                
                // Function to hide error overlay
                function hideError() {
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                }
                
                if (Hls.isSupported()) {
                    const hls = new Hls({
                        debug: true, // Enable debug to see what's happening
                        enableWorker: true,
                        // Critical buffer settings - modified to fix stalling
                        maxBufferSize: 0, // Disable buffer size limitation
                        maxBufferLength: 30, // Set reasonable buffer length
                        maxMaxBufferLength: 600, // Allow very large buffers when needed
                        highBufferWatchdogPeriod: 0, // Disable high buffer watchdog
                        // Media settings
                        backBufferLength: 90, // Keep more data in back buffer
                        nudgeOffset: 0.2, // Larger nudge offset
                        nudgeMaxRetry: 30, // More nudge retries
                        maxFragLookUpTolerance: 0.5, // More tolerance in fragment lookup
                        // Fragment loading settings
                        fragLoadingTimeOut: 60000, // Longer timeout for fragment loading
                        fragLoadingMaxRetry: 15, // More retries for fragment loading
                        fragLoadingRetryDelay: 500, // Shorter retry delay
                        fragLoadingMaxRetryTimeout: 10000, // Cap retry timeout
                        // Playlist loading
                        manifestLoadingTimeOut: 30000, // Longer timeout for manifest loading
                        manifestLoadingMaxRetry: 8, // More retries
                        manifestLoadingRetryDelay: 1000, // Reasonable retry delay
                        // Error recovery
                        appendErrorMaxRetry: 10, // More appending error retries
                        // Playback settings
                        startPosition: -1, // Start at live edge
                        startLevel: 0, // Start with lowest quality
                        startFragPrefetch: true, // Prefetch fragments
                        // ABR settings
                        abrEwmaDefaultEstimate: 1000000, // Higher default bandwidth estimate
                        abrBandWidthFactor: 0.8, // More conservative bandwidth usage
                        abrBandWidthUpFactor: 0.5, // More conservative up-switching
                        // Advanced settings
                        progressive: false, // Disable progressive parsing
                        lowLatencyMode: false, // Disable low latency mode
                        fLoader: undefined, // Use default loader
                        cmcd: undefined, // No CMCD
                        // Stream controller settings
                        maxStarvationDelay: 6, // Higher starvation delay
                        maxLoadingDelay: 6, // Higher loading delay
                        // XHR setup for playlists
                        xhrSetup: function(xhr, url) {
                            xhr.addEventListener('error', function() {
                                console.error('XHR error for URL:', url);
                            });
                            
                            // Add cache busting for playlist files
                            if (url.includes('m3u8')) {
                                // Force no-cache for m3u8 requests
                                xhr.setRequestHeader('Cache-Control', 'no-cache');
                                xhr.setRequestHeader('Pragma', 'no-cache');
                                xhr.setRequestHeader('Expires', '0');
                                const separator = url.includes('?') ? '&' : '?';
                                const cacheBuster = `${separator}_=${Date.now()}`;
                                xhr.open('GET', url + cacheBuster, true);
                                return;
                            }
                            xhr.open('GET', url, true);
                        },
                        // Override default fragment loader to add retries
                        fragLoadPolicy: {
                            default: {
                                maxTimeToFirstByteMs: 10000,
                                maxLoadTimeMs: 120000,
                                timeoutRetry: {
                                    maxNumRetry: 4,
                                    retryDelayMs: 1000,
                                    maxRetryDelayMs: 8000,
                                    backoff: 'linear'
                                },
                                errorRetry: {
                                    maxNumRetry: 6,
                                    retryDelayMs: 1000,
                                    maxRetryDelayMs: 8000,
                                    backoff: 'linear'
                                }
                            }
                        }
                    });
                    
                    // Emergency recovery handling for buffer stalls
                    let bufferStallCount = 0;
                    let lastBufferCheck = Date.now();
                    let bufferMonitorInterval;
                    
                    // Set up special buffer monitor for stall detection
                    function setupBufferMonitor() {
                        clearInterval(bufferMonitorInterval);
                        
                        let lastPlayPosition = video.currentTime;
                        let stallStartTime = 0;
                        let isStalled = false;
                        
                        // Check every 1 second
                        bufferMonitorInterval = setInterval(() => {
                            if (video.paused) {
                                return; // Skip checks for paused video
                            }
                            
                            const now = Date.now();
                            const currentPosition = video.currentTime;
                            const timeSinceLastCheck = now - lastBufferCheck;
                            
                            // Check if playback is advancing
                            if (Math.abs(currentPosition - lastPlayPosition) < 0.01) {
                                if (!isStalled) {
                                    isStalled = true;
                                    stallStartTime = now;
                                    console.warn(`[Stream ${index}] Potential stall detected at ${currentPosition}`);
                                } else {
                                    // Calculate how long we've been stalled
                                    const stallDuration = (now - stallStartTime) / 1000;
                                    
                                    // Take recovery actions based on stall duration
                                    if (stallDuration > 2 && stallDuration <= 4) {
                                        // Try light recovery after 2 seconds of stall
                                        console.warn(`[Stream ${index}] Buffer stall for ${stallDuration}s - trying light recovery`);
                                        hls.startLoad(); // Restart loading from current position
                                        bufferStallCount++;
                                    } else if (stallDuration > 4 && stallDuration <= 10) {
                                        // Try medium recovery after 4 seconds of stall
                                        console.warn(`[Stream ${index}] Buffer stall for ${stallDuration}s - trying medium recovery`);
                                        // Try to skip ahead slightly
                                        try {
                                            video.currentTime += 0.5; // Try to skip ahead 0.5s
                                        } catch(e) {}
                                        hls.recoverMediaError();
                                        bufferStallCount++;
                                    } else if (stallDuration > 10) {
                                        // Try heavy recovery after 10 seconds of stall
                                        console.warn(`[Stream ${index}] Buffer stall for ${stallDuration}s - trying emergency recovery`);
                                        // Full emergency recovery - destroy and recreate
                                        try {
                                            // Hide error message during recovery
                                            hideError();
                                            // Store current time if possible
                                            const currentTime = video.currentTime;
                                            // Destroy the current hls instance
                                            hls.destroy();
                                            // Create a new stream with fresh parameters
                                            setTimeout(() => {
                                                initializeStream(index, playlistUrl + '?_emergency=' + Date.now());
                                                // Try to restore position if appropriate
                                                if (currentTime > 5) {
                                                    setTimeout(() => {
                                                        try {
                                                            const newVideo = document.getElementById('video-' + index);
                                                            if (newVideo && newVideo.readyState > 1) {
                                                                newVideo.currentTime = currentTime;
                                                            }
                                                        } catch(e) {}
                                                    }, 2000);
                                                }
                                            }, 500);
                                            
                                            // Stop this monitoring interval as we're recreating the player
                                            clearInterval(bufferMonitorInterval);
                                        } catch(e) {
                                            console.error(`[Stream ${index}] Failed emergency recovery:`, e);
                                        }
                                    }
                                }
                            } else {
                                // Playback is advancing normally
                                if (isStalled) {
                                    console.info(`[Stream ${index}] Recovered from stall`);
                                    isStalled = false;
                                }
                                lastPlayPosition = currentPosition;
                            }
                            
                            lastBufferCheck = now;
                        }, 1000);
                    }
                    
                    // Handle specific HLS.js events
                    
                    // Handle fatal errors
                    hls.on(Hls.Events.ERROR, function(event, data) {
                        console.warn(`[Stream ${index}] HLS error:`, data);
                        
                        if (data.fatal) {
                            switch(data.type) {
                                case Hls.ErrorTypes.NETWORK_ERROR:
                                    console.error(`[Stream ${index}] Fatal network error:`, data.details);
                                    showError('Network error: ' + data.details);
                                    
                                    // Try to recover network error
                                    setTimeout(() => {
                                        console.info(`[Stream ${index}] Attempting network recovery`);
                                        hls.startLoad();
                                    }, 1000);
                                    break;
                                    
                                case Hls.ErrorTypes.MEDIA_ERROR:
                                    console.error(`[Stream ${index}] Fatal media error:`, data.details);
                                    showError('Media error: ' + data.details);
                                    
                                    // Try to recover media error
                                    setTimeout(() => {
                                        console.info(`[Stream ${index}] Attempting media recovery`);
                                        hls.recoverMediaError();
                                    }, 1000);
                                    break;
                                    
                                default:
                                    // Cannot recover from other fatal errors
                                    console.error(`[Stream ${index}] Fatal error:`, data.details);
                                    showError('Fatal error: ' + data.details);
                                    
                                    // Try to recover by recreating everything
                                    setTimeout(() => {
                                        console.info(`[Stream ${index}] Attempting full player recreation`);
                                        hideError();
                                        hls.destroy();
                                        initializeStream(index, playlistUrl + '?_fatal=' + Date.now());
                                    }, 2000);
                                    break;
                            }
                        } else if (data.details === Hls.ErrorDetails.BUFFER_STALLED_ERROR) {
                            // Handle non-fatal buffer stall
                            console.warn(`[Stream ${index}] Buffer stalled:`, data);
                            bufferStallCount++;
                            
                            if (bufferStallCount > 3) {
                                // If we've stalled multiple times, try more aggressive recovery
                                console.warn(`[Stream ${index}] Multiple buffer stalls (${bufferStallCount}), trying recovery`);
                                hls.recoverMediaError();
                            }
                        } else if (data.details === "fragParsingError") {
                            // Handle fragment parsing errors
                            console.warn(`[Stream ${index}] Fragment parsing error:`, data);
                            
                            // Try to skip the problematic fragment
                            setTimeout(() => {
                                console.info(`[Stream ${index}] Attempting to skip problematic fragment`);
                                hls.stopLoad();
                                hls.startLoad(-1); // Restart from live point
                            }, 500);
                        }
                    });
                    
                    // Monitor when fragments are loaded
                    hls.on(Hls.Events.FRAG_LOADED, function(event, data) {
                        console.info(`[Stream ${index}] Fragment loaded:`, data.frag.sn);
                        hideError(); // Hide error when fragments load successfully
                    });
                    
                    // Monitor when buffer is appended
                    hls.on(Hls.Events.BUFFER_APPENDED, function(event, data) {
                        console.info(`[Stream ${index}] Buffer appended: ${data.type}, ${data.timeRanges[data.type].length} ranges`);
                    });
                    
                    // Monitor when manifest is loaded
                    hls.on(Hls.Events.MANIFEST_PARSED, function(event, data) {
                        console.info(`[Stream ${index}] Manifest parsed: ${data.levels.length} levels`);
                        hideError();
                        
                        // Setup our custom buffer monitor
                        setupBufferMonitor();
                        
                        // Attempt to play the video with fallback
                        const playPromise = video.play();
                        if (playPromise !== undefined) {
                            playPromise.catch(error => {
                                console.warn(`[Stream ${index}] Autoplay prevented:`, error);
                                
                                // Add a play button overlay for user interaction
                                const container = video.parentElement;
                                const playButton = document.createElement('div');
                                playButton.style.position = 'absolute';
                                playButton.style.top = '50%';
                                playButton.style.left = '50%';
                                playButton.style.transform = 'translate(-50%, -50%)';
                                playButton.style.width = '80px';
                                playButton.style.height = '80px';
                                playButton.style.borderRadius = '50%';
                                playButton.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                                playButton.style.cursor = 'pointer';
                                playButton.style.zIndex = '30';
                                playButton.style.display = 'flex';
                                playButton.style.alignItems = 'center';
                                playButton.style.justifyContent = 'center';
                                playButton.innerHTML = '<svg width="40" height="40" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>';
                                playButton.addEventListener('click', function() {
                                    video.play();
                                    this.remove();
                                });
                                container.appendChild(playButton);
                            });
                        }
                    });
                    
                    // Clean up when video is destroyed
                    video.addEventListener('emptied', function() {
                        if (bufferMonitorInterval) {
                            clearInterval(bufferMonitorInterval);
                        }
                    });
                    
                    // Load the stream
                    console.info(`[Stream ${index}] Loading stream URL:`, playlistUrl);
                    hls.loadSource(playlistUrl);
                    hls.attachMedia(video);
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    // Native HLS support (Safari)
                    console.log('Using native HLS support for stream ' + index);
                    video.src = playlistUrl;
                    
                    // Improve native playback performance
                    video.preload = 'auto';
                    video.playsInline = true;
                    
                    // Set playback attributes to reduce stalling
                    video.addEventListener('canplay', function() {
                        // Lower latency for live streams
                        if ('mediaSettings' in video) {
                            try {
                                video.mediaSettings = {
                                    preferredLatency: 'low',
                                    preferredBackBuffer: 30
                                };
                            } catch (e) {
                                console.warn('Cannot set media settings:', e);
                            }
                        }
                    });
                    
                    video.addEventListener('loadedmetadata', function() {
                        hideError();
                        video.play().catch(e => console.error('Autoplay prevented for stream ' + index, e));
                    });
                    
                    // Handle errors for native playback
                    video.addEventListener('error', function(e) {
                        showError('Video playback error: ' + (video.error ? video.error.message : 'Unknown error'));
                        console.error('Video playback error for stream ' + index + ':', video.error);
                        
                        // Retry with cache busting
                        setTimeout(() => {
                            hideError();
                            const cacheBuster = playlistUrl.includes('?') ? '&' : '?';
                            video.src = playlistUrl + cacheBuster + '_=' + Date.now();
                            video.load();
                            video.play().catch(e => console.error('Retry autoplay prevented for stream ' + index, e));
                        }, 3000);
                    });
                } else {
                    console.error('HLS not supported for stream ' + index);
                    showError('HLS playback not supported in this browser');
                }
            }

            // Add audio toggle functionality
            window.toggleAudio = function(index) {
                const video = document.getElementById('video-' + index);
                const button = video.parentElement.querySelector('.audio-toggle');
                
                if (video) {
                    video.muted = !video.muted;
                    button.classList.toggle('muted', video.muted);
                    
                    // Update button icon
                    const path = button.querySelector('svg path');
                    if (video.muted) {
                        path.setAttribute('d', 'M11 5L6 9H2v6h4l5 4V5zM17 9.82v4.36c.84-.3 1.5-1.07 1.5-2.18S17.84 10.12 17 9.82z');
                    } else {
                        path.setAttribute('d', 'M11 5L6 9H2v6h4l5 4V5zm6.93 4.93c1.88 1.88 1.88 4.92 0 6.8-1.88 1.88-4.92 1.88-6.8 0-1.88-1.88-1.88-4.92 0-6.8 1.88-1.88 4.92-1.88 6.8 0z');
                    }
                    
                    // Mute all other videos when unmuting one
                    if (!video.muted) {
                        document.querySelectorAll('video').forEach(v => {
                            if (v.id !== video.id) {
                                v.muted = true;
                                const otherButton = v.parentElement.querySelector('.audio-toggle');
                                if (otherButton) {
                                    otherButton.classList.add('muted');
                                    const otherPath = otherButton.querySelector('svg path');
                                    otherPath.setAttribute('d', 'M11 5L6 9H2v6h4l5 4V5zM17 9.82v4.36c.84-.3 1.5-1.07 1.5-2.18S17.84 10.12 17 9.82z');
                                }
                            }
                        });
                    }
                }
            };
            
            // Initialize all videos as muted
            document.querySelectorAll('video').forEach(video => {
                video.muted = true;
            });
        });
    </script>
    
    <!-- Status check script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusBtn = document.getElementById('check-status-btn');
            const statusDialog = document.getElementById('status-dialog');
            const closeBtn = document.getElementById('close-status-dialog');
            const statusResults = document.getElementById('status-results');
            
            if (!statusBtn) return;
            
            // Format JSON for display
            function formatJSON(json) {
                try {
                    // Return a formatted string with indentation and colored syntax
                    const obj = typeof json === 'string' ? JSON.parse(json) : json;
                    return JSON.stringify(obj, null, 2);
                } catch (e) {
                    return json.toString();
                }
            }
            
            // Function to check stream status
            async function checkStreamStatus() {
                const layoutId = {{ $layout->id }};
                statusResults.innerHTML = 'Checking stream status...';
                statusDialog.style.display = 'flex';
                
                try {
                    const response = await fetch(`/api/view/multiview-status/${layoutId}`);
                    
                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status} ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    console.log('Stream status result:', data);
                    
                    // Format and display the results
                    statusResults.innerHTML = formatJSON(data);
                    
                    // Check for any error conditions
                    if (data.stream_statuses) {
                        let hasIssues = false;
                        
                        data.stream_statuses.forEach((streamStatus, index) => {
                            if (streamStatus.is_active && streamStatus.files_status) {
                                if (!streamStatus.files_status.directory_exists || 
                                    !streamStatus.files_status.playlist_exists || 
                                    !streamStatus.files_status.has_enough_segments) {
                                    
                                    // Highlight the issue by adding a retry action
                                    hasIssues = true;
                                    
                                    // This stream has issues, retry it if possible
                                    const streamElement = document.getElementById(`video-${index}`);
                                    const errorElement = document.getElementById(`error-${index}`);
                                    if (streamElement && errorElement) {
                                        console.log(`Stream ${index} has issues:`, streamStatus.files_status);
                                        
                                        // Show the error for this stream
                                        errorElement.style.display = 'flex';
                                        const errorText = errorElement.querySelector('p');
                                        if (errorText) {
                                            errorText.textContent = 'Stream files not found. Try reloading.';
                                        }
                                    }
                                }
                            }
                        });
                        
                        if (hasIssues) {
                            statusResults.innerHTML += '\n\nIssues detected with one or more streams. Check the highlighted streams.';
                        } else {
                            statusResults.innerHTML += '\n\nAll active streams appear to be in good condition.';
                        }
                    }
                    
                } catch (error) {
                    console.error('Error checking stream status:', error);
                    statusResults.innerHTML = `Error checking stream status: ${error.message}`;
                }
            }
            
            // Open dialog and check status when button clicked
            statusBtn.addEventListener('click', checkStreamStatus);
            
            // Close dialog when close button clicked
            closeBtn.addEventListener('click', function() {
                statusDialog.style.display = 'none';
            });
            
            // Close dialog when clicking outside
            statusDialog.addEventListener('click', function(event) {
                if (event.target === statusDialog) {
                    statusDialog.style.display = 'none';
                }
            });
            
            // Escape key closes dialog
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && statusDialog.style.display === 'flex') {
                    statusDialog.style.display = 'none';
                }
            });
        });
    </script>
    
    <!-- Motion detection and volume visualizer script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Audio processing setup
            let audioContext = null;
            const visualizers = {};
            const motionDetectors = {};
            const volumeMeters = {}; // For volume meters
            const BARS_COUNT = 8; // Number of equalizer bars
            const MOTION_SENSITIVITY = 8; // Motion detection sensitivity (lower = more sensitive)
            const MOTION_THRESHOLD = 10; // Threshold for motion detection (% of pixels that need to change)
            
            // Performance mode control
            const performanceToggle = document.getElementById('performance-mode');
            let visualEffectsEnabled = true; // Always enable visual effects by default
            
            if (performanceToggle) {
                performanceToggle.checked = true; // Default to visual effects ON
                performanceToggle.addEventListener('change', function() {
                    visualEffectsEnabled = this.checked;
                    toggleVisualEffects(visualEffectsEnabled);
                });
            }
            
            // Function to enable/disable visual effects globally
            function toggleVisualEffects(enabled) {
                // Display or hide motion indicators
                document.querySelectorAll('.motion-indicator').forEach(indicator => {
                    indicator.style.display = enabled ? 'block' : 'none';
                });
                
                // Display or hide visualizers
                document.querySelectorAll('.volume-visualizer').forEach(visualizer => {
                    visualizer.style.display = enabled ? 'flex' : 'none';
                });
                
                // Stop or start motion detection
                if (!enabled) {
                    // Stop all motion detection
                    Object.keys(motionDetectors).forEach(streamId => {
                        if (motionDetectors[streamId] && motionDetectors[streamId].animationFrameId) {
                            cancelAnimationFrame(motionDetectors[streamId].animationFrameId);
                            motionDetectors[streamId].animationFrameId = null;
                        }
                    });
                    
                    // Stop audio processing
                    Object.keys(visualizers).forEach(streamId => {
                        if (visualizers[streamId]) {
                            if (visualizers[streamId].animationFrameId) {
                                cancelAnimationFrame(visualizers[streamId].animationFrameId);
                                visualizers[streamId].animationFrameId = null;
                            }
                            if (visualizers[streamId].simulationInterval) {
                                clearInterval(visualizers[streamId].simulationInterval);
                                visualizers[streamId].simulationInterval = null;
                            }
                        }
                    });
                } else {
                    // Start for all videos with proper readyState
                    document.querySelectorAll('video').forEach(video => {
                        if (video.readyState >= 2) {
                            const streamId = video.id.replace('video-', '');
                            if (audioContext) {
                                setupAudioAnalyzer(streamId, video);
                            } else {
                                setupSimulation(streamId);
                            }
                            setupMotionDetection(streamId, video);
                        }
                    });
                }
            }
            
            // Initialize audio context on user interaction (required by browsers)
            document.addEventListener('click', initAudioContext, { once: true });
            
            function initAudioContext() {
                if (!visualEffectsEnabled) return;
                
                try {
                    audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    console.log('AudioContext initialized');
                    
                    // Setup all existing video elements
                    document.querySelectorAll('video').forEach(video => {
                        if (video.readyState >= 2) { // HAVE_CURRENT_DATA or higher
                            const streamId = video.id.replace('video-', '');
                            setupAudioAnalyzer(streamId, video);
                            setupMotionDetection(streamId, video);
                        }
                    });
                } catch (error) {
                    console.error('Error initializing AudioContext:', error);
                    // If real audio analysis fails, set up simulation for all streams
                    document.querySelectorAll('.volume-visualizer').forEach(visualizer => {
                        const streamId = visualizer.id.replace('visualizer-', '');
                        setupSimulation(streamId);
                    });
                }
            }
            
            // Handle video elements created after initialization
            document.addEventListener('DOMNodeInserted', function(e) {
                if (!visualEffectsEnabled) return;
                
                if (e.target.tagName === 'VIDEO' && e.target.id) {
                    const streamId = e.target.id.replace('video-', '');
                    const video = document.getElementById('video-' + streamId);
                    
                    // Setup simulation first for immediate visual feedback
                    setupSimulation(streamId);
                    
                    // When video loads, switch to real audio analysis if possible
                    video.addEventListener('loadeddata', function() {
                        if (!visualEffectsEnabled) return;
                        
                        if (audioContext) {
                            setupAudioAnalyzer(streamId, video);
                        }
                        setupMotionDetection(streamId, video);
                    });
                }
            });
            
            // Setup motion detection for a stream
            function setupMotionDetection(streamId, videoElement) {
                if (!visualEffectsEnabled) return;
                
                const motionIndicator = document.getElementById('motion-indicator-' + streamId);
                const debugCanvas = document.getElementById('motion-debug-' + streamId);
                
                if (!motionIndicator || !debugCanvas) {
                    console.warn('Motion detection elements not found for stream', streamId);
                    return;
                }
                
                console.log('Setting up motion detection for stream', streamId);
                
                // Clean up existing detector if any
                if (motionDetectors[streamId] && motionDetectors[streamId].animationFrameId) {
                    cancelAnimationFrame(motionDetectors[streamId].animationFrameId);
                }
                
                // Set canvas dimensions to match video container (not video itself)
                const container = videoElement.parentElement;
                debugCanvas.width = container.clientWidth;
                debugCanvas.height = container.clientHeight;
                
                const context = debugCanvas.getContext('2d', { willReadFrequently: true });
                const compareContext = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
                
                // For efficiency, we'll detect motion at a lower resolution
                const detectionWidth = 80;  // Increased from 64 for better detection
                const detectionHeight = 60; // Increased from 48 for better detection
                compareContext.canvas.width = detectionWidth;
                compareContext.canvas.height = detectionHeight;
                
                let previousImageData = null;
                let motionLevel = 0;
                const frameSampleRate = 3; // Process more frames (lower number = more frequent)
                let frameCount = 0;
                let lastProcessTime = 0;
                
                motionDetectors[streamId] = {
                    animationFrameId: null,
                    motionLevel: 0,
                    streamActive: true,
                    lastMotionTime: 0
                };
                
                // Function to calculate motion between frames
                function detectMotion(timestamp) {
                    if (!visualEffectsEnabled) {
                        motionIndicator.classList.remove('motion-active');
                        return;
                    }
                    
                    try {
                        frameCount++;
                        
                        // Process more frequently for better responsiveness (changed from 100ms to 50ms)
                        if (timestamp - lastProcessTime < 50 || frameCount % frameSampleRate !== 0) {
                            motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                            return;
                        }
                        
                        lastProcessTime = timestamp;
                        
                        // Skip if video element is not ready or paused
                        if (videoElement.readyState < 2 || videoElement.paused) {
                            motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                            return;
                        }
                        
                        // Draw current video frame to canvas at reduced resolution
                        try {
                            compareContext.drawImage(videoElement, 0, 0, detectionWidth, detectionHeight);
                        } catch (e) {
                            motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                            return;
                        }
                        
                        // Get image data
                        let currentImageData;
                        try {
                            currentImageData = compareContext.getImageData(0, 0, detectionWidth, detectionHeight);
                        } catch (e) {
                            motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                            return;
                        }
                        
                        // Skip first frame (no previous frame to compare)
                        if (previousImageData === null) {
                            previousImageData = currentImageData;
                            motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                            return;
                        }
                        
                        // Compare pixel data between frames - more pixels for better detection
                        const current = currentImageData.data;
                        const previous = previousImageData.data;
                        let changedPixels = 0;
                        
                        // Sample more pixels (reduced stride from 16 to 12)
                        for (let i = 0; i < current.length; i += 12) {
                            // Only check luminance (faster than RGB comparison)
                            const currentLuma = 0.299 * current[i] + 0.587 * current[i+1] + 0.114 * current[i+2];
                            const previousLuma = 0.299 * previous[i] + 0.587 * previous[i+1] + 0.114 * previous[i+2];
                            
                            if (Math.abs(currentLuma - previousLuma) > MOTION_SENSITIVITY) {
                                changedPixels++;
                            }
                        }
                        
                        // Calculate percentage of pixels that changed
                        const pixelCount = (detectionWidth * detectionHeight) / 4;
                        const percentChanged = (changedPixels / pixelCount) * 100;
                        
                        // Update motion level (0-100) with increased multiplier for better response
                        motionLevel = Math.min(100, percentChanged * 2.5); // Increased from 2 to 2.5
                        motionDetectors[streamId].motionLevel = motionLevel;
                        
                        // Store when we last detected significant motion
                        if (motionLevel > 15) {
                            motionDetectors[streamId].lastMotionTime = Date.now();
                        }
                        
                        // Update motion indicator with more details
                        if (percentChanged > MOTION_THRESHOLD) {
                            if (!motionIndicator.classList.contains('motion-active')) {
                                motionIndicator.classList.add('motion-active');
                                console.log(`Motion detected in stream ${streamId}: ${percentChanged.toFixed(1)}%`);
                            }
                            
                            // Optional: Visualize motion in the debug canvas for testing
                            if (false) { // Set to true for debug visualization
                                context.fillStyle = 'rgba(255, 0, 0, 0.3)';
                                context.fillRect(0, 0, debugCanvas.width, debugCanvas.height);
                                debugCanvas.style.opacity = '0.3';
                            }
                            
                            // Boost audio visualization based on motion detection
                            if (visualizers[streamId] && visualizers[streamId].targetLevels) {
                                const boostFactor = Math.min(2, 1 + (percentChanged / 100));
                                for (let i = 0; i < visualizers[streamId].targetLevels.length; i++) {
                                    visualizers[streamId].targetLevels[i] = Math.min(100, 
                                        visualizers[streamId].targetLevels[i] * boostFactor);
                                }
                            }
                            
                            // DIRECT SYNC: Immediately update audio meter for real-time response
                            if (audioLevelMeters[streamId]) {
                                // Only boost if significant motion detected
                                if (percentChanged > 8) {
                                    // Calculate boost from motion - stronger effect
                                    const audioBoost = Math.min(70, percentChanged * 1.5);
                                    
                                    // Apply boost immediately to current audio level
                                    audioLevelMeters[streamId].currentLevel = Math.max(
                                        audioLevelMeters[streamId].currentLevel,
                                        30 + audioBoost
                                    );
                                    
                                    // Force visual update now for immediate effect
                                    audioLevelMeters[streamId].element.style.height = 
                                        audioLevelMeters[streamId].currentLevel + '%';
                                }
                            }
                        } else if (motionIndicator.classList.contains('motion-active')) {
                            motionIndicator.classList.remove('motion-active');
                            debugCanvas.style.opacity = '0';
                        }
                        
                        // Store current frame for next comparison
                        previousImageData = currentImageData;
                        
                    } catch (error) {
                        console.error('Error in motion detection:', error);
                    }
                    
                    // Continue the detection loop
                    motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                }
                
                // Start motion detection
                motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                
                // Update motion indicator immediately so it's visible
                motionIndicator.style.display = 'block';
                
                // Update motion indicator and reset on errors
                videoElement.addEventListener('error', () => {
                    motionIndicator.classList.remove('motion-active');
                    motionDetectors[streamId].streamActive = false;
                });
                
                videoElement.addEventListener('play', () => {
                    if (!visualEffectsEnabled) return;
                    
                    motionDetectors[streamId].streamActive = true;
                    previousImageData = null; // Reset comparison on play
                    
                    // Force audio meter response on play event for real-time feedback
                    if (audioLevelMeters[streamId]) {
                        audioLevelMeters[streamId].targetLevel = 50 + Math.random() * 30;
                        audioLevelMeters[streamId].currentLevel = Math.max(
                            audioLevelMeters[streamId].currentLevel, 
                            40 + Math.random() * 20
                        );
                        
                        // Force visual update
                        audioLevelMeters[streamId].element.style.height = 
                            audioLevelMeters[streamId].currentLevel + '%';
                    }
                    
                    // Restart motion detection if it was stopped
                    if (!motionDetectors[streamId].animationFrameId) {
                        motionDetectors[streamId].animationFrameId = requestAnimationFrame(detectMotion);
                    }
                });
                
                videoElement.addEventListener('pause', () => {
                    // No need to process frames when video is paused
                    if (motionDetectors[streamId] && motionDetectors[streamId].animationFrameId) {
                        cancelAnimationFrame(motionDetectors[streamId].animationFrameId);
                        motionDetectors[streamId].animationFrameId = null;
                    }
                    
                    // Reduce audio level immediately when video pauses
                    if (audioLevelMeters[streamId]) {
                        audioLevelMeters[streamId].targetLevel = 15;
                        // Gradual transition down
                        const currentLevel = audioLevelMeters[streamId].currentLevel;
                        audioLevelMeters[streamId].currentLevel = Math.max(15, currentLevel * 0.7);
                        
                        // Update DOM
                        audioLevelMeters[streamId].element.style.height = 
                            audioLevelMeters[streamId].currentLevel + '%';
                    }
                });
            }
            
            // Setup real-time audio analyzer for a stream (only called when visualEffectsEnabled is true)
            function setupAudioAnalyzer(streamId, videoElement) {
                if (!audioContext || !visualEffectsEnabled) return;
                
                // Get the visualizer element
                const visualizer = document.getElementById('visualizer-' + streamId);
                if (!visualizer) return;
                
                console.log('Setting up audio analyzer for stream', streamId);
                
                // Stop any existing simulation
                if (visualizers[streamId] && visualizers[streamId].simulationInterval) {
                    clearInterval(visualizers[streamId].simulationInterval);
                    visualizers[streamId].simulationInterval = null;
                }
                
                // Only create new analyzer if we don't already have one
                if (!visualizers[streamId] || !visualizers[streamId].analyzer) {
                    try {
                        // Create media source from video element
                        const source = audioContext.createMediaElementSource(videoElement);
                        
                        // Create analyzer
                        const analyzer = audioContext.createAnalyser();
                        analyzer.fftSize = 128; // Reduced for better performance
                        analyzer.smoothingTimeConstant = 0.5;
                        
                        // Connect source to analyzer and then to destination (speakers)
                        source.connect(analyzer);
                        source.connect(audioContext.destination);
                        
                        // Get all equalizer bars
                        const bars = Array.from(visualizer.querySelectorAll('.equalizer-bar'));
                        
                        // Create data array for analyzer
                        const dataArray = new Uint8Array(analyzer.frequencyBinCount);
                        
                        // Store references
                        visualizers[streamId] = {
                            analyzer: analyzer,
                            dataArray: dataArray,
                            bars: bars,
                            isSimulated: false,
                            simulationInterval: null,
                            animationFrameId: null,
                            lastUpdateTime: 0
                        };
                    } catch (error) {
                        console.error('Error setting up audio analyzer for stream', streamId, error);
                        // Fall back to simulation
                        setupSimulation(streamId);
                        return;
                    }
                }
                
                // Start analysis loop if not already running
                if (!visualizers[streamId].animationFrameId) {
                    // Create the update function
                    function updateBars(timestamp) {
                        if (!visualEffectsEnabled) return;
                        
                        // Limit updates to reduce CPU impact
                        if (timestamp - visualizers[streamId].lastUpdateTime < 100) {
                            visualizers[streamId].animationFrameId = requestAnimationFrame(updateBars);
                            return;
                        }
                        
                        visualizers[streamId].lastUpdateTime = timestamp;
                        
                        // Get frequency data
                        visualizers[streamId].analyzer.getByteFrequencyData(visualizers[streamId].dataArray);
                        
                        // Process data for visualization (simplify frequency data to match bar count)
                        const dataArray = visualizers[streamId].dataArray;
                        const bars = visualizers[streamId].bars;
                        const frequencyStep = Math.floor(dataArray.length / bars.length);
                        
                        for (let i = 0; i < bars.length; i++) {
                            // Get average value for frequency segment
                            let sum = 0;
                            const startIndex = i * frequencyStep;
                            
                            for (let j = 0; j < frequencyStep; j++) {
                                sum += dataArray[startIndex + j];
                            }
                            
                            const average = sum / frequencyStep;
                            
                            // Factor in motion level if available
                            let height = average / 255 * 100; // Base height from audio
                            
                            if (motionDetectors[streamId] && motionDetectors[streamId].motionLevel > 0) {
                                // Boost visualization based on motion level
                                const motionBoost = motionDetectors[streamId].motionLevel / 100;
                                height = Math.min(100, height * (1 + motionBoost * 0.5));
                            }
                            
                            // Ensure minimum height
                            height = Math.max(3, height);
                            
                            // Apply height to bar
                            bars[i].style.height = height + '%';
                        }
                        
                        // Continue loop
                        visualizers[streamId].animationFrameId = requestAnimationFrame(updateBars);
                    }
                    
                    visualizers[streamId].animationFrameId = requestAnimationFrame(updateBars);
                }
            }
            
            // Setup a simulated equalizer when real audio isn't available
            function setupSimulation(streamId) {
                if (!visualEffectsEnabled) return;
                
                // Get the visualizer element
                const visualizer = document.getElementById('visualizer-' + streamId);
                if (!visualizer) return;
                
                // If already set up with real analyzer, don't override
                if (visualizers[streamId] && !visualizers[streamId].isSimulated) return;
                
                console.log('Setting up simulated visualizer for stream', streamId);
                
                // Clear any existing simulation
                if (visualizers[streamId] && visualizers[streamId].simulationInterval) {
                    clearInterval(visualizers[streamId].simulationInterval);
                    cancelAnimationFrame(visualizers[streamId].animationFrameId);
                }
                
                // Get all equalizer bars
                const bars = Array.from(visualizer.querySelectorAll('.equalizer-bar'));
                
                // Initialize with random values
                const barLevels = bars.map(() => Math.random() * 40 + 5);
                const targetLevels = [...barLevels];
                const speeds = bars.map(() => Math.random() * 1 + 0.5);
                
                // Store references
                visualizers[streamId] = {
                    bars: bars,
                    barLevels: barLevels,
                    targetLevels: targetLevels,
                    speeds: speeds,
                    isSimulated: true,
                    simulationInterval: null,
                    animationFrameId: null,
                    updateCount: 0
                };
                
                // Create a lifelike simulation of audio visualizer, but with reduced frequency
                visualizers[streamId].simulationInterval = setInterval(() => {
                    if (!visualEffectsEnabled) return;
                    
                    visualizers[streamId].updateCount++;
                    
                    // Only change target levels occasionally to reduce CPU impact
                    if (visualizers[streamId].updateCount % 3 === 0) {
                        // Occasionally change the target levels to create patterns
                        if (Math.random() < 0.2) { // 20% chance per interval
                            for (let i = 0; i < targetLevels.length; i++) {
                                // Create a more lifelike pattern with frequency distribution
                                // (lower frequencies typically have more energy)
                                const position = i / (targetLevels.length - 1); // 0 to 1
                                const baseLevel = Math.max(0, 70 - position * 50); // Higher levels for lower frequencies
                                
                                // Add randomness
                                targetLevels[i] = Math.min(100, Math.max(5, 
                                    baseLevel + (Math.random() * 30 - 15)
                                ));
                            }
                        }
                        
                        // Occasionally add a beat-like pulse
                        if (Math.random() < 0.05) { // 5% chance
                            // Add a surge to all or some bars
                            for (let i = 0; i < targetLevels.length; i++) {
                                if (Math.random() < 0.7) { // 70% chance for each bar
                                    targetLevels[i] = Math.min(100, targetLevels[i] + Math.random() * 40);
                                }
                            }
                        }
                        
                        // Factor in motion detection if available
                        if (motionDetectors[streamId] && motionDetectors[streamId].motionLevel > 0) {
                            const motionBoost = motionDetectors[streamId].motionLevel / 100;
                            for (let i = 0; i < targetLevels.length; i++) {
                                targetLevels[i] = Math.min(100, targetLevels[i] * (1 + motionBoost));
                            }
                        }
                    }
                    
                    // Apply movements toward target levels
                    for (let i = 0; i < barLevels.length; i++) {
                        // Move current level toward target
                        const diff = targetLevels[i] - barLevels[i];
                        barLevels[i] += diff * speeds[i] * 0.2;
                        
                        // Apply to DOM
                        bars[i].style.height = barLevels[i] + '%';
                    }
                }, 100); // Increased interval to reduce updates
            }
            
            // Set high performance mode by default explicitly
            toggleVisualEffects(true);
            
            // Initialize motion detection for all videos immediately
            document.querySelectorAll('video').forEach(video => {
                const streamId = video.id.replace('video-', '');
                console.log('Setting up initial motion detection for video', streamId);
                setupMotionDetection(streamId, video);
            });

            // Initialize audio level meters for all videos
            const audioLevelMeters = {};
            
            // Updates to ensure real-time response
            function enableRealTimeUpdates() {
                console.log("Enabling real-time updates for all meters");
                // Force refresh all meters in case of stalling
                Object.keys(audioLevelMeters).forEach(streamId => {
                    const meter = audioLevelMeters[streamId];
                    if (meter && meter.isSimulated) {
                        if (meter.animationFrameId) {
                            cancelAnimationFrame(meter.animationFrameId);
                        }
                        // Restart simulation with fresh timing
                        meter.lastUpdateTime = Date.now();
                        startAudioLevelSimulation(streamId);
                    }
                });
            }
            
            // Call real-time updates initially and periodically
            enableRealTimeUpdates();
            setInterval(enableRealTimeUpdates, 30000); // Refresh every 30 seconds
            
            // Also refresh meters when tab becomes visible again
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    enableRealTimeUpdates();
                }
            });

            // Function to synchronize video motion with audio levels
            function syncVideoMotionWithAudio() {
                // Check every 50ms for better real-time synchronization
                setInterval(function() {
                    // For each video with active motion detection
                    Object.keys(motionDetectors).forEach(streamId => {
                        const motionData = motionDetectors[streamId];
                        const audioMeter = audioLevelMeters[streamId];
                        const videoElement = document.getElementById('video-' + streamId);
                        
                        // Only process active videos
                        const videoActive = videoElement && 
                                           !videoElement.paused && 
                                           videoElement.readyState >= 2 && 
                                           !videoElement.ended;
                        
                        if (!videoActive) {
                            // Video not active - reset motion effects
                            if (audioMeter) {
                                audioMeter.motionBoostActive = false;
                                audioMeter.motionBoostAmount = 0;
                                audioMeter.active = false;
                                
                                // Ensure video has no highlight border
                                if (videoElement) {
                                    videoElement.style.borderColor = '';
                                }
                                
                                // Set to minimum level after a short delay
                                if (audioMeter.currentLevel > 5) {
                                    // Gradual fadeout
                                    audioMeter.currentLevel = Math.max(5, audioMeter.currentLevel * 0.9);
                                    audioMeter.element.style.height = audioMeter.currentLevel + '%';
                                }
                            }
                            return;
                        }
                        
                        if (motionData && audioMeter && motionData.streamActive) {
                            // Update audio meter active state based on video
                            audioMeter.active = videoActive;
                            
                            // Get the current motion level (0-100)
                            const motionLevel = motionData.motionLevel || 0;
                            
                            // If we have significant motion, boost the audio meter
                            if (motionLevel > 5 && videoActive) {
                                // Apply a stronger motion-to-audio coupling
                                // Motion directly influences the audio level target
                                audioMeter.motionBoostActive = true;
                                audioMeter.motionBoostAmount = Math.min(50, motionLevel * 0.7);
                                
                                // IMPORTANT: Directly modify audio level for immediate effect
                                audioMeter.currentLevel = Math.max(audioMeter.currentLevel, 
                                    Math.min(95, 30 + (motionLevel * 0.7)));
                                
                                // Force visual update immediately for real-time appearance
                                audioMeter.element.style.height = audioMeter.currentLevel + '%';
                                
                                // Visual indicator that motion is affecting audio
                                if (videoElement && motionLevel > 20) {
                                    videoElement.style.borderColor = 'rgba(255,165,0,0.7)';
                                } else if (videoElement) {
                                    videoElement.style.borderColor = '';
                                }
                            } else {
                                // Reset motion boost when no significant motion
                                audioMeter.motionBoostActive = false;
                                audioMeter.motionBoostAmount = 0;
                                
                                // Reset video border
                                if (videoElement) {
                                    videoElement.style.borderColor = '';
                                }
                            }
                        }
                    });
                }, 50);
            }
            
            // Apply video-audio synchronization
            syncVideoMotionWithAudio();
            
            // Set up audio level meter for each stream (improved version)
            function setupAudioLevelMeter(streamId) {
                const fillElement = document.getElementById(`audio-level-fill-${streamId}`);
                
                if (!fillElement) {
                    console.warn('Audio level meter element not found for stream', streamId);
                    return;
                }
                
                // Store meter references with enhanced properties
                audioLevelMeters[streamId] = {
                    element: fillElement,
                    simulationTimer: null,
                    animationFrameId: null,
                    isSimulated: true,
                    lastUpdateTime: Date.now(),
                    currentLevel: 30,
                    targetLevel: 40,
                    trendDirection: 1,
                    trendStrength: 0.3,
                    peakHoldTime: 0,
                    lastPeakLevel: 0,
                    streamId: streamId,
                    motionBoostActive: false,
                    motionBoostAmount: 0,
                    active: false, // Track if the audio meter should be active
                    // Bind update function to meter object
                    update: updateMeter
                };
                
                // Start with simulation
                startAudioLevelSimulation(streamId);
                
                // Bind video events to better sync with audio
                const videoElement = document.getElementById('video-' + streamId);
                if (videoElement) {
                    // Check initial state
                    audioLevelMeters[streamId].active = !videoElement.paused && videoElement.readyState >= 2;
                    
                    // React to video events
                    videoElement.addEventListener('play', function() {
                        if (audioLevelMeters[streamId]) {
                            console.log(`Stream ${streamId} playing - activating audio meter`);
                            // Mark as active
                            audioLevelMeters[streamId].active = true;
                            
                            // Increase audio activity when video starts playing
                            audioLevelMeters[streamId].targetLevel = Math.max(
                                audioLevelMeters[streamId].targetLevel, 
                                40 + Math.random() * 30
                            );
                            
                            // Force restart if needed
                            if (!audioLevelMeters[streamId].animationFrameId) {
                                audioLevelMeters[streamId].lastUpdateTime = Date.now();
                                audioLevelMeters[streamId].animationFrameId = 
                                    requestAnimationFrame(audioLevelMeters[streamId].update);
                            }
                        }
                    });
                    
                    videoElement.addEventListener('pause', function() {
                        if (audioLevelMeters[streamId]) {
                            console.log(`Stream ${streamId} paused - reducing audio meter`);
                            // Mark as inactive
                            audioLevelMeters[streamId].active = false;
                            
                            // Gradually decrease audio when video pauses
                            audioLevelMeters[streamId].targetLevel = 15;
                            audioLevelMeters[streamId].currentLevel = Math.max(5, audioLevelMeters[streamId].currentLevel * 0.4);
                            
                            // Update DOM immediately
                            audioLevelMeters[streamId].element.style.height = 
                                audioLevelMeters[streamId].currentLevel + '%';
                                
                            // Stop animation after a quick fadeout
                            setTimeout(function() {
                                if (!audioLevelMeters[streamId].active) {
                                    // Only if still inactive
                                    if (audioLevelMeters[streamId].animationFrameId) {
                                        cancelAnimationFrame(audioLevelMeters[streamId].animationFrameId);
                                        audioLevelMeters[streamId].animationFrameId = null;
                                    }
                                    
                                    // Set to minimum value when stopped
                                    audioLevelMeters[streamId].currentLevel = 5;
                                    audioLevelMeters[streamId].element.style.height = '5%';
                                }
                            }, 800);
                        }
                    });
                    
                    // Also check for video errors and stopped state
                    videoElement.addEventListener('error', function() {
                        if (audioLevelMeters[streamId]) {
                            console.log(`Stream ${streamId} error - deactivating audio meter`);
                            audioLevelMeters[streamId].active = false;
                            
                            // Set to minimum level
                            audioLevelMeters[streamId].currentLevel = 5;
                            audioLevelMeters[streamId].element.style.height = '5%';
                            
                            // Stop animation
                            if (audioLevelMeters[streamId].animationFrameId) {
                                cancelAnimationFrame(audioLevelMeters[streamId].animationFrameId);
                                audioLevelMeters[streamId].animationFrameId = null;
                            }
                        }
                    });
                    
                    // Check for ended state
                    videoElement.addEventListener('ended', function() {
                        if (audioLevelMeters[streamId]) {
                            console.log(`Stream ${streamId} ended - deactivating audio meter`);
                            audioLevelMeters[streamId].active = false;
                            
                            // Set to minimum level
                            audioLevelMeters[streamId].currentLevel = 5;
                            audioLevelMeters[streamId].element.style.height = '5%';
                            
                            // Stop animation
                            if (audioLevelMeters[streamId].animationFrameId) {
                                cancelAnimationFrame(audioLevelMeters[streamId].animationFrameId);
                                audioLevelMeters[streamId].animationFrameId = null;
                            }
                        }
                    });
                    
                    // Monitor video readyState changes
                    videoElement.addEventListener('waiting', function() {
                        if (audioLevelMeters[streamId]) {
                            console.log(`Stream ${streamId} waiting - reducing audio meter activity`);
                            // Reduce activity but don't stop completely while buffering
                            audioLevelMeters[streamId].targetLevel = Math.min(
                                audioLevelMeters[streamId].targetLevel,
                                20 + Math.random() * 10
                            );
                        }
                    });
                }
            }
            
            // Enhanced update meter function with video motion integration
            function updateMeter() {
                const now = Date.now();
                const deltaTime = Math.min((now - this.lastUpdateTime) / 1000, 0.1);
                this.lastUpdateTime = now;
                
                // Check if the associated video is still active
                const videoElement = document.getElementById('video-' + this.streamId);
                const videoActive = videoElement && 
                                    !videoElement.paused && 
                                    videoElement.readyState >= 2 && 
                                    !videoElement.ended;
                
                // Update active state based on video status
                this.active = videoActive;
                
                // If video is inactive, gradually reduce audio level and potentially stop animation
                if (!videoActive) {
                    this.targetLevel = Math.max(5, this.targetLevel * 0.9);
                    this.currentLevel = Math.max(5, this.currentLevel * 0.95);
                    
                    // Apply to DOM
                    this.element.style.height = this.currentLevel + '%';
                    
                    // If level is very low, stop the animation
                    if (this.currentLevel < 7) {
                        this.element.style.height = '5%';
                        return; // Don't schedule next frame
                    }
                    
                    // Otherwise continue animation for smooth fadeout
                    this.animationFrameId = requestAnimationFrame(this.update);
                    return;
                }
                
                // For more realistic meter, check if we have video motion data
                let videoBoost = 0;
                const streamId = this.streamId;
                
                if (this.motionBoostActive && this.motionBoostAmount > 0) {
                    // Video motion is directly causing audio level increases
                    videoBoost = this.motionBoostAmount;
                    
                    // Add more peaks during motion for realism
                    if (Math.random() < 0.25) {
                        const peakSize = Math.random() * this.motionBoostAmount / 25;
                        this.targetLevel = Math.min(95, this.targetLevel + peakSize * 30);
                    }
                }
                
                // Always apply trend-based changes
                if (Math.random() < 0.15) {
                    this.trendDirection = Math.random() > 0.5 ? 1 : -1;
                    this.trendStrength = Math.random() * 0.8;
                }
                
                // Adjust target level based on trend and video boost
                this.targetLevel += this.trendDirection * this.trendStrength * 15 * deltaTime;
                this.targetLevel = Math.max(15, Math.min(95, this.targetLevel + videoBoost));
                
                // Process peaks based on combined audio/video activity
                if (Math.random() < 0.12 + (videoBoost / 200)) {
                    const peakSize = Math.random();
                    if (peakSize > 0.7) {
                        this.targetLevel = 85 + Math.random() * 15;
                    } else if (peakSize > 0.4) {
                        this.targetLevel = 65 + Math.random() * 20;
                    } else {
                        this.targetLevel = 50 + Math.random() * 15;
                    }
                }
                
                // Dynamic attack/release based on video motion
                let attackSpeed = 0.85;
                let releaseSpeed = 0.45;
                
                // Faster attack during video motion for better sync
                if (videoBoost > 0) {
                    attackSpeed = Math.min(0.95, attackSpeed + (videoBoost / 100));
                    releaseSpeed = Math.min(0.7, releaseSpeed + (videoBoost / 200));
                }
                
                // Apply calculated speeds
                if (this.targetLevel > this.currentLevel) {
                    this.currentLevel += (this.targetLevel - this.currentLevel) * attackSpeed;
                } else {
                    this.currentLevel += (this.targetLevel - this.currentLevel) * releaseSpeed;
                }
                
                // Apply peaks and peak hold
                if (this.currentLevel > this.lastPeakLevel) {
                    this.lastPeakLevel = this.currentLevel;
                    this.peakHoldTime = 0.5;
                } else {
                    this.peakHoldTime -= deltaTime;
                    if (this.peakHoldTime <= 0) {
                        this.lastPeakLevel = Math.max(this.currentLevel, this.lastPeakLevel - 25 * deltaTime);
                    }
                }
                
                // Apply to DOM with a more direct approach for better performance
                this.element.style.height = this.currentLevel + '%';
                
                // Schedule next update
                this.animationFrameId = requestAnimationFrame(this.update);
            }
            
            // Initialize audio level meters for all streams
            document.querySelectorAll('video').forEach(video => {
                const streamId = video.id.replace('video-', '');
                setupAudioLevelMeter(streamId);
            });
            
            // Set up simulation for audio level meters (improved)
            function startAudioLevelSimulation(streamId) {
                const meter = audioLevelMeters[streamId];
                if (!meter) return;
                
                // Clean up any existing simulation
                if (meter.simulationTimer) {
                    clearInterval(meter.simulationTimer);
                }
                
                if (meter.animationFrameId) {
                    cancelAnimationFrame(meter.animationFrameId);
                }
                
                // Mark as simulated
                meter.isSimulated = true;
                meter.lastUpdateTime = Date.now();
                
                // Set initial level immediately
                meter.element.style.height = meter.currentLevel + '%';
                
                // Start the animation loop with the bound update method
                meter.update = updateMeter.bind(meter);
                meter.animationFrameId = requestAnimationFrame(meter.update);
                
                // Backup interval in case requestAnimationFrame stalls
                meter.simulationTimer = setInterval(function() {
                    if (Date.now() - meter.lastUpdateTime > 100) {
                        console.log('Restarting stalled audio level meter animation for stream', streamId);
                        if (meter.animationFrameId) {
                            cancelAnimationFrame(meter.animationFrameId);
                        }
                        meter.lastUpdateTime = Date.now();
                        meter.animationFrameId = requestAnimationFrame(meter.update);
                    }
                }, 150); // More frequent check for better responsiveness
            }

            // Add event listeners for the floating controls
            const checkStatusFloating = document.getElementById('check-status-floating');
            const fullscreenFloating = document.getElementById('fullscreen-floating');
            
            if (checkStatusFloating) {
                checkStatusFloating.addEventListener('click', function() {
                    // Trigger the same function as the main check status button
                    document.getElementById('check-status-btn').click();
                });
            }
            
            if (fullscreenFloating) {
                fullscreenFloating.addEventListener('click', function() {
                    // Trigger the same function as the main fullscreen button
                    document.getElementById('fullscreen-btn').click();
                });
            }

            // Add event listeners for the sidebar controls
            const checkStatusSidebar = document.getElementById('check-status-sidebar');
            const fullscreenSidebar = document.getElementById('fullscreen-sidebar');
            
            // Function to toggle fullscreen
            function toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            }

            // Set up event listeners for the sidebar controls
            if (checkStatusSidebar) {
                checkStatusSidebar.addEventListener('click', function() {
                    showStatusDialog();
                });
            }
            
            if (fullscreenSidebar) {
                fullscreenSidebar.addEventListener('click', function() {
                    toggleFullscreen();
                });
            }
            
            // Function to show status dialog
            function showStatusDialog() {
                const statusDialog = document.getElementById('status-dialog');
                if (statusDialog) {
                    console.log('Showing status dialog');
                    statusDialog.style.display = 'flex'; // Change to flex to make it visible with proper alignment
                    checkStreamStatus();
                } else {
                    console.warn('Status dialog element not found');
                }
            }

            // Function to check stream status
            function checkStreamStatus() {
                console.log('Checking stream status');
                const statusResults = document.getElementById('status-results');
                if (statusResults) {
                    statusResults.innerHTML = '<p>Checking stream status...</p>';
                    
                    // Get all stream elements
                    const streamElements = document.querySelectorAll('.stream-container');
                    let results = '';
                    
                    streamElements.forEach((element, index) => {
                        const streamName = element.querySelector('.stream-label')?.textContent || `Stream ${index + 1}`;
                        const video = element.querySelector('video');
                        
                        if (video && video.paused) {
                            results += `<div class="status-result status-error"><span>${streamName}</span>: Not playing</div>`;
                        } else if (video) {
                            results += `<div class="status-result status-ok"><span>${streamName}</span>: Playing</div>`;
                        } else {
                            results += `<div class="status-result status-unknown"><span>${streamName}</span>: Unknown status</div>`;
                        }
                    });
                    
                    statusResults.innerHTML = results;
                }
            }
            
            // Close status dialog button
            const closeStatusDialog = document.getElementById('close-status-dialog');
            if (closeStatusDialog) {
                closeStatusDialog.addEventListener('click', function() {
                    console.log('Closing status dialog');
                    const statusDialog = document.getElementById('status-dialog');
                    if (statusDialog) {
                        statusDialog.style.display = 'none';
                    }
                });
            }
        });
    </script>
    
    <script>
        // Add this function near the top of your first script tag
        function showStatusDialogDirect() {
            console.log('Check Status button clicked directly');
            const statusDialog = document.getElementById('status-dialog');
            if (statusDialog) {
                statusDialog.style.display = 'flex';
                
                // Check all streams
                const statusResults = document.getElementById('status-results');
                if (statusResults) {
                    statusResults.innerHTML = '<p>Checking stream status...</p>';
                    
                    // Get all stream elements
                    const streamElements = document.querySelectorAll('.stream-container');
                    let results = '';
                    
                    streamElements.forEach((element, index) => {
                        const streamName = element.querySelector('.stream-label')?.textContent || `Stream ${index + 1}`;
                        const video = element.querySelector('video');
                        
                        if (video && video.paused) {
                            results += `<div class="status-result status-error"><span>${streamName}</span>: Not playing</div>`;
                        } else if (video) {
                            results += `<div class="status-result status-ok"><span>${streamName}</span>: Playing</div>`;
                        } else {
                            results += `<div class="status-result status-unknown"><span>${streamName}</span>: Unknown status</div>`;
                        }
                    });
                    
                    statusResults.innerHTML = results || '<p>No streams found</p>';
                }
            } else {
                console.error('Status dialog not found');
            }
        }
        
        // Add this for the close button too
        document.getElementById('close-status-dialog')?.addEventListener('click', function() {
            const statusDialog = document.getElementById('status-dialog');
            if (statusDialog) {
                statusDialog.style.display = 'none';
            }
        });
    </script>
    
    <!-- Add automatic channel recovery for stopped videos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-recovery for stopped channels
            function setupAutoRecovery() {
                console.log('Setting up automatic channel recovery');
                
                // Check for stopped videos every 10 seconds
                setInterval(function() {
                    console.log('Checking for stopped streams...');
                    const videoElements = document.querySelectorAll('video');
                    
                    videoElements.forEach((video, index) => {
                        const streamId = video.id.replace('video-', '');
                        const streamContainer = video.closest('.stream-container');
                        
                        // Check if this stream has content but is not playing
                        if (streamContainer && !streamContainer.querySelector('.stream-placeholder') && 
                            (video.paused || video.ended || video.readyState < 2 || video.error)) {
                            
                            console.warn(`Stream ${streamId} appears to be stopped - attempting automatic restart`);
                            
                            // Get the stream URL from the script that initialized it
                            let streamUrl = null;
                            const errorElement = document.getElementById(`error-${streamId}`);
                            
                            // Find button with retryStream function call to extract URL
                            if (errorElement) {
                                const retryButton = errorElement.querySelector('.retry-button');
                                if (retryButton) {
                                    const onclickAttr = retryButton.getAttribute('onclick');
                                    if (onclickAttr) {
                                        // Extract URL from the onclick attribute
                                        const match = onclickAttr.match(/retryStream\(['"]\d+['"],\s*['"]([^'"]+)['"]\)/);
                                        if (match && match[1]) {
                                            streamUrl = match[1];
                                        }
                                    }
                                }
                            }
                            
                            // If we found the URL, retry the stream
                            if (streamUrl) {
                                console.log(`Auto-restarting stream ${streamId} with URL: ${streamUrl}`);
                                // Use the existing retry function
                                retryStream(streamId, streamUrl);
                            } else {
                                console.error(`Could not find URL to restart stream ${streamId}`);
                            }
                        }
                    });
                }, 10000); // Check every 10 seconds
            }
            
            // Start auto-recovery system
            setupAutoRecovery();
        });
    </script>
</body>
</html> 