<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $stream['name'] }} - TV Channel Monitoring</title>
    
    <!-- Include HLS.js for video playback -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    
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
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .multiview-container {
            position: relative;
            width: 100vw;
            height: calc(100vh - 60px); /* Adjust for header height */
            overflow: hidden;
            background-color: #000000;
        }
        
        .stream-container {
            position: absolute;
            overflow: hidden;
            background-color: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: #666;
            text-align: center;
        }
        
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        
        .stream-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 600px;
            z-index: 100;
        }
        
        .stream-error h2 {
            color: #e74c3c;
            margin-top: 0;
        }
        
        .stream-error p {
            color: #ddd;
            margin-bottom: 0;
        }
        
        /* Center the video when in a cell */
        .stream-container video {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $stream['name'] }}</h1>
        <div class="controls">
            @if($isLiveStream)
                <span class="status-indicator status-live">● LIVE</span>
                @if(isset($segmentCount))
                    <span class="status-indicator">Segments: {{ $segmentCount }}</span>
                @endif
            @else
                <span class="status-indicator status-preview">● PREVIEW</span>
            @endif
            <a href="{{ route('view.streams') }}" class="back-button">Back to Streams</a>
        </div>
    </div>
    
    <div class="multiview-container" id="multiview-container">
        <div class="stream-container" style="
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        ">
            @if($stream['input_name'])
                <div class="stream-label label-bottom">
                    {{ $stream['input_name'] }}
                    @if(isset($segmentCount) && $segmentCount > 0)
                        <span class="segment-count">({{ $segmentCount }} segments)</span>
                    @endif
                </div>
            @endif
            
            @if($stream['has_stream'])
                @if($isLiveStream)
                    <div id="video-container" class="relative h-full w-full">
                        <video 
                            id="hls-video" 
                            class="w-full h-full bg-black" 
                            controls 
                            autoplay 
                            muted 
                            playsinline>
                        </video>
                        <div id="error-overlay" class="hidden absolute inset-0 bg-black bg-opacity-80 flex items-center justify-center text-white p-4">
                            <div class="text-center">
                                <h3 class="text-xl font-bold mb-2">Stream Error</h3>
                                <p id="error-message">Unable to load the stream. It may be offline or experiencing technical difficulties.</p>
                                <button id="retry-button" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Retry</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="stream-placeholder">{{ $stream['input_name'] ?? 'No stream' }}</div>
                @endif
            @else
                <div class="stream-placeholder">
                    No stream
                    @if(isset($hasSegments) && $hasSegments)
                        <div class="segment-info">
                            Found {{ $segmentCount }} segments but stream is not ready. 
                            The playlist might be generating.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    @if($isLiveStream && empty($playlistUrl))
        <div class="stream-error">
            <h2>Stream Not Available</h2>
            <p>The stream is marked as active but no playlist file was found.</p>
            @if(isset($hasSegments) && $hasSegments)
                <p>Found {{ $segmentCount }} segments, but the playlist is not ready yet.</p>
            @endif
        </div>
    @endif

    @if(isset($hasSegments) && $hasSegments && !$stream['has_stream'])
        <div class="stream-error">
            <h2>Stream Processing</h2>
            <p>Found {{ $segmentCount }} video segments, but the stream is not fully ready yet.</p>
            <p>This usually means the stream is still initializing. Please wait or refresh the page.</p>
            <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Refresh</button>
        </div>
    @endif
    
    @if($isLiveStream && $playlistUrl)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('hls-video');
                const errorOverlay = document.getElementById('error-overlay');
                const errorMessage = document.getElementById('error-message');
                const retryButton = document.getElementById('retry-button');
                
                // Segment numbering format is: segment_XXX.ts
                const playlistUrl = "{{ $playlistUrl }}";
                console.log("Using playlist URL:", playlistUrl);
                
                let hls = null;
                
                // Retry mechanism
                let retryCount = 0;
                const MAX_RETRIES = 5;
                
                function showError(message) {
                    errorMessage.textContent = message;
                    errorOverlay.classList.remove('hidden');
                }
                
                function hideError() {
                    errorOverlay.classList.add('hidden');
                }
                
                function initPlayer() {
                    hideError();
                    retryCount = 0;
                    
                    if (Hls.isSupported()) {
                        if (hls) {
                            hls.destroy();
                        }
                        
                        hls = new Hls({
                            debug: true,
                            enableWorker: true,
                            lowLatencyMode: false,
                            backBufferLength: 60,
                            maxBufferLength: 60,
                            maxMaxBufferLength: 60,
                            maxBufferSize: 60 * 1000 * 1000,
                            maxBufferHole: 1.0,
                            highBufferWatchdogPeriod: 2,
                            liveSyncDurationCount: 3,
                            liveMaxLatencyDurationCount: 10,
                            liveDurationInfinity: false,
                            startFragPrefetch: true,
                            testBandwidth: true,
                            abrEwmaDefaultEstimate: 1000000,
                            manifestLoadingTimeOut: 20000,
                            manifestLoadingMaxRetry: 6,
                            manifestLoadingRetryDelay: 1000,
                            fragLoadingTimeOut: 20000,
                            fragLoadingMaxRetry: 6,
                            fragLoadingRetryDelay: 1000,
                            discontinuityMode: true,
                            tsTimestampTolleranceMs: 1000,
                            xhrSetup: function(xhr, url) {
                                console.log("HLS requesting URL:", url);
                                
                                if (url.includes('segment')) {
                                    let fixedUrl = url;
                                    
                                    if (!url.match(/\/segment\/\d+\.ts$/)) {
                                        let segmentMatch = url.match(/segment[/_]?(\d+)\.ts$/) || url.match(/(\d+)\.ts$/);
                                        if (segmentMatch && segmentMatch[1]) {
                                            let segNum = parseInt(segmentMatch[1]);
                                            let baseUrl = url.substring(0, url.lastIndexOf('/') + 1);
                                            
                                            if (!baseUrl.endsWith('segment/')) {
                                                baseUrl = baseUrl.replace(/segment\/?$/, '') + 'segment/';
                                            }
                                            
                                            fixedUrl = baseUrl + segNum + '.ts';
                                            console.log("Fixed segment URL:", url, " → ", fixedUrl);
                                            xhr.open('GET', fixedUrl, true);
                                            return;
                                        }
                                    }
                                }
                                
                                const separator = url.includes('?') ? '&' : '?';
                                const cacheBuster = `${separator}_=${Date.now()}`;
                                xhr.open('GET', url + cacheBuster, true);
                                
                                xhr.setRequestHeader('Cache-Control', 'no-cache');
                                xhr.setRequestHeader('Pragma', 'no-cache');
                            }
                        });
                        
                        hls.loadSource(playlistUrl);
                        hls.attachMedia(video);
                        
                        hls.on(Hls.Events.ERROR, function(event, data) {
                            console.error('HLS error:', data);
                            
                            if (data.details && data.details.includes('FRAG_')) {
                                console.log("Fragment error:", data.details, data.frag ? data.frag.url : 'unknown fragment');
                                
                                // Specifically handle fragParsingError
                                if (data.details === 'fragParsingError') {
                                    console.log("Fragment parsing error detected, attempting recovery");
                                    
                                    // Try to skip the problematic fragment
                                    setTimeout(() => {
                                        hls.stopLoad();
                                        hls.startLoad(-1); // Restart from live point
                                    }, 500);
                                    return;
                                }
                                
                                // Handle TS discontinuity errors
                                if (data.details === 'bufferStalledError' || 
                                    data.details === 'bufferAddCodecError' || 
                                    data.details.includes('discontinuity') || 
                                    (data.error && data.error.message && data.error.message.includes('discontinuity'))) {
                                    
                                    console.log("Detected TS discontinuity-related error, attempting recovery");
                                    
                                    // For discontinuity errors, we need a more aggressive recovery approach
                                    setTimeout(() => {
                                        // First try to skip past the problematic fragment
                                        if (data.frag && hls.currentLevel !== -1) {
                                            const level = hls.levels[hls.currentLevel];
                                            const currentFragIndex = level.fragments.indexOf(data.frag);
                                            if (currentFragIndex >= 0 && currentFragIndex < level.fragments.length - 1) {
                                                const nextFrag = level.fragments[currentFragIndex + 1];
                                                console.log("Skipping to next fragment:", nextFrag.url);
                                                hls.startLoad(nextFrag.start / 1000);
                                                return;
                                            }
                                        }
                                        
                                        // If we can't skip to next fragment, try full recovery
                                        recoverFromStall();
                                    }, 500);
                                    return;
                                }
                                
                                // Specific handling for buffer stalled error
                                if (data.details === 'bufferStalledError') {
                                    console.log("Buffer stall detected, attempting recovery");
                                    recoverFromStall();
                                    return;
                                }
                                
                                if (data.response && (data.response.code === 404 || data.response.code === 403)) {
                                    console.log("Attempting recovery from segment error");
                                    
                                    setTimeout(() => {
                                        hls.stopLoad();
                                        hls.startLoad(-1);
                                    }, 1000);
                                    return;
                                }
                            }
                            
                            if (data.fatal) {
                                switch(data.type) {
                                    case Hls.ErrorTypes.NETWORK_ERROR:
                                        if (data.response && data.response.code === 404) {
                                            showError("Stream segments not found. The stream may have ended or is being generated.");
                                        } else if (data.response && data.response.code === 403) {
                                            showError("Access to stream denied. Please check permissions.");
                                        } else {
                                            showError("Network error. Trying to recover...");
                                        }
                                        
                                        if (retryCount < MAX_RETRIES) {
                                            retryCount++;
                                            console.log(`Retry attempt ${retryCount}...`);
                                            setTimeout(() => hls.loadSource(playlistUrl + '?_=' + Date.now()), 3000);
                                        } else {
                                            showError("Unable to load the stream after several attempts. Please try again later.");
                                        }
                                        break;
                                        
                                    case Hls.ErrorTypes.MEDIA_ERROR:
                                        showError("Media error. Trying to recover...");
                                        hls.recoverMediaError();
                                        break;
                                        
                                    default:
                                        showError("Fatal error. Unable to continue playback.");
                                        break;
                                }
                            }
                        });
                        
                        hls.on(Hls.Events.MANIFEST_PARSED, function() {
                            console.log("HLS manifest loaded successfully");
                            video.play().catch(e => console.error('Autoplay prevented:', e));
                        });
                        
                        hls.on(Hls.Events.FRAG_LOADED, function(event, data) {
                            console.log("Fragment loaded successfully:", data.frag.url);
                        });
                        
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = playlistUrl;
                        video.addEventListener('error', function(e) {
                            console.error('Video playback error:', e);
                            showError("Video playback error. The stream may be offline.");
                        });
                        
                        video.play().catch(e => console.error('Autoplay prevented:', e));
                    } else {
                        showError("Your browser doesn't support HLS playback.");
                    }
                }
                
                // Check for stalled playback and try to recover
                let lastPlayPosition = 0;
                let stallCount = 0;
                let bufferCheckTimer = null;
                
                // More aggressive stall detection and recovery
                function setupBufferMonitoring() {
                    if (bufferCheckTimer) {
                        clearInterval(bufferCheckTimer);
                    }
                    
                    let lastBufferLevel = 0;
                    let bufferStagnantCount = 0;
                    
                    bufferCheckTimer = setInterval(() => {
                        if (!video.paused) {
                            // Check if video is stalled
                            if (video.readyState > 1 && video.currentTime === lastPlayPosition) {
                                stallCount++;
                                console.log(`Playback stalled (${stallCount}/3)`);
                                
                                if (stallCount >= 3) { // Act faster on stalls
                                    console.log("Detected stalled playback, attempting recovery");
                                    recoverFromStall();
                                    stallCount = 0;
                                }
                            } else {
                                stallCount = 0;
                            }
                            
                            // Check buffer status
                            if (hls && video.buffered.length) {
                                const bufferEnd = video.buffered.end(video.buffered.length - 1);
                                const bufferAhead = bufferEnd - video.currentTime;
                                
                                console.log(`Buffer status: ${bufferAhead.toFixed(2)}s ahead of current time`);
                                
                                // Check if buffer is stagnant (not growing)
                                if (Math.abs(bufferAhead - lastBufferLevel) < 0.01) {
                                    bufferStagnantCount++;
                                    console.log(`Buffer appears stagnant (${bufferStagnantCount}/5)`);
                                    
                                    if (bufferStagnantCount >= 5) {
                                        console.log("Buffer hasn't grown for 5 seconds, may be stalled");
                                        if (bufferAhead < 4.0) {
                                            console.log("Proactive stall prevention - attempting recovery");
                                            recoverFromStall();
                                            bufferStagnantCount = 0;
                                        }
                                    }
                                } else {
                                    bufferStagnantCount = 0;
                                }
                                
                                lastBufferLevel = bufferAhead;
                                
                                // If buffer is getting too low, try to refill
                                if (bufferAhead < 2.0 && hls.media) {
                                    console.log("Buffer running low, requesting more data");
                                    if (!video.paused) {
                                        hls.trigger(Hls.Events.BUFFER_APPENDING);
                                        
                                        // If extremely low, maybe we need a bit more aggressive action
                                        if (bufferAhead < 0.5) {
                                            console.log("Buffer critically low, reducing playback rate");
                                            // Temporarily slow down playback to allow buffer to fill
                                            const originalRate = video.playbackRate;
                                            video.playbackRate = 0.7;
                                            
                                            // Restore normal rate after buffer improves
                                            setTimeout(() => {
                                                if (video.playbackRate !== 1.0) {
                                                    console.log("Restoring normal playback rate");
                                                    video.playbackRate = 1.0;
                                                }
                                            }, 3000);
                                        }
                                    }
                                }
                                
                                // Check if we're way behind in playback
                                if (hls.liveSyncPosition && video.currentTime < hls.liveSyncPosition - 15) {
                                    console.log("Playback too far behind live point, jumping ahead");
                                    video.currentTime = hls.liveSyncPosition - 2;
                                }
                            }
                            
                            lastPlayPosition = video.currentTime;
                        }
                    }, 1000); // Check every second
                }
                
                function recoverFromStall() {
                    if (hls && hls.media) {
                        // Try series of recovery actions in sequence
                        console.log("Recovery step 1: Flush buffer and reload");
                        hls.trigger(Hls.Events.BUFFER_FLUSHING);
                        
                        // First simply try to seek slightly ahead
                        if (video.currentTime > 0) {
                            const newPosition = Math.min(video.currentTime + 0.1, video.duration || video.currentTime + 0.1);
                            console.log(`Seeking ahead from ${video.currentTime} to ${newPosition}`);
                            video.currentTime = newPosition;
                        }
                        
                        setTimeout(() => {
                            if (video.readyState < 3) {
                                console.log("Recovery step 2: Stop and restart load");
                                
                                // Clear all data to get a fresh start
                                try {
                                    if (window.performance && window.performance.clearResourceTimings) {
                                        performance.clearResourceTimings();
                                    }
                                    
                                    // Force garbage collection if possible (not supported in all browsers)
                                    if (window.gc) {
                                        window.gc();
                                    }
                                } catch (e) {
                                    console.log("Error during cleanup:", e);
                                }
                                
                                hls.stopLoad();
                                hls.startLoad();
                                
                                // Try to resume playback
                                if (video.paused) {
                                    video.play().catch(e => console.error("Could not resume playback:", e));
                                }
                                
                                setTimeout(() => {
                                    if (video.readyState < 3) {
                                        console.log("Recovery step 3: Full player reload with new settings");
                                        
                                        // Destroy and recreate player with larger buffers
                                        if (hls) {
                                            const currentTime = video.currentTime;
                                            hls.destroy();
                                            
                                            // Wait a bit before re-initializing
                                            setTimeout(() => {
                                                initPlayer();
                                                
                                                // Try to restore position
                                                setTimeout(() => {
                                                    if (video && video.readyState > 0) {
                                                        video.currentTime = currentTime;
                                                        video.play().catch(e => console.error("Could not play:", e));
                                                    }
                                                }, 2000);
                                            }, 1000);
                                        } else {
                                            initPlayer(); // Complete reload
                                        }
                                    }
                                }, 3000);
                            }
                        }, 1000);
                    }
                }
                
                // Handle buffer stalled errors that might come from HLS.js
                if (Hls.isSupported()) {
                    document.addEventListener('hlsBufferStalled', function() {
                        console.log("HLS buffer stalled event detected");
                        recoverFromStall();
                    });
                }
                
                // Start buffer monitoring when player is ready
                video.addEventListener('canplay', function() {
                    setupBufferMonitoring();
                });
                
                // Handle retry button clicks
                retryButton.addEventListener('click', function() {
                    initPlayer();
                });
                
                // Add a button to force refresh the stream
                const refreshButton = document.createElement('button');
                refreshButton.textContent = 'Refresh Stream';
                refreshButton.className = 'mt-4 ml-2 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700';
                refreshButton.addEventListener('click', function() {
                    if (hls) {
                        console.log("Manual refresh requested");
                        forceStreamRefresh(true);
                    }
                });
                document.querySelector('.controls').appendChild(refreshButton);
                
                // Force refresh stream function
                function forceStreamRefresh(preservePosition = true) {
                    if (!hls) return;
                    
                    const currentTime = preservePosition ? video.currentTime : 0;
                    console.log("Forcing stream refresh" + (preservePosition ? " (preserving position)" : ""));
                    
                    // Stop current stream processing
                    hls.stopLoad();
                    
                    // Reset internal state
                    try {
                        // Try to clear any pending fragment loading
                        hls.trigger(Hls.Events.BUFFER_FLUSHING);
                    } catch (e) {
                        console.error("Error during buffer flush:", e);
                    }
                    
                    // Add a cache buster to the URL
                    const refreshUrl = playlistUrl + '?_=' + Date.now();
                    console.log("Reloading playlist from:", refreshUrl);
                    
                    // Load the source with cache busting
                    setTimeout(() => {
                        hls.loadSource(refreshUrl);
                        hls.startLoad();
                        
                        // Restore position if needed
                        if (preservePosition && currentTime > 0) {
                            setTimeout(() => {
                                if (video.readyState > 0) {
                                    video.currentTime = currentTime;
                                }
                            }, 1000);
                        }
                    }, 500);
                }
                
                // Initialize the player
                initPlayer();
                
                // Force periodic manifest reload to ensure fresh content
                let manifestReloadTimer = setInterval(() => {
                    if (hls && !video.paused) {
                        // Check if we should do a full refresh or just reload the manifest
                        if (video.readyState < 3 || stallCount > 0) {
                            console.log("Low readyState or stalls detected, performing full refresh");
                            forceStreamRefresh(true);
                        } else {
                            console.log("Performing periodic manifest reload");
                            // Just reload the manifest without disturbing playback
                            hls.loadSource(playlistUrl + '?_=' + Date.now());
                        }
                    }
                }, 20000); // Reload manifest every 20 seconds during playback (reduced from 30s)
                
                // Clean up resources when leaving the page
                window.addEventListener('beforeunload', () => {
                    if (hls) {
                        hls.destroy();
                    }
                    if (manifestReloadTimer) {
                        clearInterval(manifestReloadTimer);
                    }
                    if (bufferCheckTimer) {
                        clearInterval(bufferCheckTimer);
                    }
                });
            });
        </script>
    @endif
</body>
</html> 