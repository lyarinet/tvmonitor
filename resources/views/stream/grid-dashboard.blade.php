<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grid Dashboard - TV Channel Monitoring</title>
    <style>
        :root {
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --dark-bg: #1a1a1a;
            --card-bg: #2d2d2d;
            --border-color: #444;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--dark-bg);
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .dashboard-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .header-title h1 {
            margin: 0;
            font-size: 24px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .grid-preview {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            min-height: 300px;
            position: relative;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 4px;
        }

        .preview-cell {
            background-color: var(--dark-bg);
            border: 1px solid var(--border-color);
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .preview-cell:hover {
            border-color: var(--primary-color);
        }

        .preview-cell.active {
            border-color: var(--success-color);
        }

        .preview-cell.error {
            border-color: var(--danger-color);
        }

        .status-indicator {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--success-color);
        }

        .status-indicator.offline {
            background-color: var(--danger-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: var(--dark-bg);
            border-radius: 6px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #888;
        }

        .action-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            color: white;
        }

        .primary-button {
            background-color: var(--primary-color);
        }

        .success-button {
            background-color: var(--success-color);
        }

        .warning-button {
            background-color: var(--warning-color);
        }

        .danger-button {
            background-color: var(--danger-color);
        }

        .action-button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .stream-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .stream-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .stream-item:last-child {
            border-bottom: none;
        }

        .stream-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stream-actions {
            display: flex;
            gap: 5px;
        }

        .small-button {
            padding: 4px 8px;
            font-size: 12px;
        }

        .error-log {
            background-color: var(--dark-bg);
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            max-height: 200px;
            overflow-y: auto;
        }

        .error-entry {
            margin-bottom: 8px;
            padding: 8px;
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 3px solid var(--danger-color);
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                flex-direction: column;
            }

            .grid-preview {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-title">
                <h1>Grid Layout Dashboard</h1>
            </div>
            <div class="header-actions">
                <button class="action-button primary-button" onclick="checkAllStreams()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Check All Streams
                </button>
                <button class="action-button success-button" onclick="restartAllStreams()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                    </svg>
                    Restart All
                </button>
                <a href="{{ route('view.multiview', ['id' => $layout->id]) }}" class="action-button warning-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="20" rx="2" ry="2"/>
                        <line x1="12" y1="6" x2="12" y2="18"/>
                        <line x1="6" y1="12" x2="18" y2="12"/>
                    </svg>
                    View Grid
                </a>
            </div>
        </div>

        <div class="grid-preview">
            @foreach($streamData as $index => $stream)
                <div class="preview-cell {{ $stream['has_stream'] ? 'active' : '' }}" 
                     onclick="showStreamDetails({{ $index }})">
                    {{ $stream['input_name'] ?? 'No Stream' }}
                    @if($stream['has_stream'])
                        <div class="status-indicator {{ $stream['is_active'] ? '' : 'offline' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Stream Statistics</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ collect($streamData)->where('has_stream', true)->count() }}</div>
                        <div class="stat-label">Active Streams</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ collect($streamData)->where('has_stream', false)->count() }}</div>
                        <div class="stat-label">Empty Slots</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ collect($streamData)->where('is_active', true)->count() }}</div>
                        <div class="stat-label">Online</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ collect($streamData)->where('has_stream', true)->where('is_active', false)->count() }}</div>
                        <div class="stat-label">Offline</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Stream List</h2>
                </div>
                <div class="stream-list">
                    @foreach($streamData as $index => $stream)
                        @if($stream['has_stream'])
                            <div class="stream-item">
                                <div class="stream-info">
                                    <div class="status-indicator {{ $stream['is_active'] ? '' : 'offline' }}"></div>
                                    <span>{{ $stream['input_name'] }}</span>
                                </div>
                                <div class="stream-actions">
                                    <button class="action-button small-button primary-button" 
                                            onclick="checkStream({{ $index }})">Check</button>
                                    <button class="action-button small-button success-button" 
                                            onclick="restartStream({{ $index }})">Restart</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Error Log</h2>
                    <button class="action-button small-button danger-button" onclick="clearErrorLog()">Clear</button>
                </div>
                <div class="error-log" id="errorLog">
                    <!-- Error logs will be populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to check all streams
        async function checkAllStreams() {
            try {
                const response = await fetch(`/api/view/multiview-status/{{ $layout->id }}`);
                const data = await response.json();
                updateStreamStatus(data);
                logMessage('Checked all streams', 'info');
            } catch (error) {
                logMessage('Error checking streams: ' + error.message, 'error');
            }
        }

        // Function to restart all streams
        async function restartAllStreams() {
            try {
                const response = await fetch(`/api/view/multiview-restart/{{ $layout->id }}`, {
                    method: 'POST'
                });
                const data = await response.json();
                logMessage('Restarted all streams', 'info');
                setTimeout(checkAllStreams, 5000); // Check status after 5 seconds
            } catch (error) {
                logMessage('Error restarting streams: ' + error.message, 'error');
            }
        }

        // Function to check individual stream
        async function checkStream(index) {
            try {
                const response = await fetch(`/api/view/stream-status/${index}`);
                const data = await response.json();
                updateStreamStatus({ stream_statuses: { [index]: data } });
                logMessage(`Checked stream ${index}`, 'info');
            } catch (error) {
                logMessage(`Error checking stream ${index}: ${error.message}`, 'error');
            }
        }

        // Function to restart individual stream
        async function restartStream(index) {
            try {
                const response = await fetch(`/api/view/stream-restart/${index}`, {
                    method: 'POST'
                });
                const data = await response.json();
                logMessage(`Restarted stream ${index}`, 'info');
                setTimeout(() => checkStream(index), 5000); // Check status after 5 seconds
            } catch (error) {
                logMessage(`Error restarting stream ${index}: ${error.message}`, 'error');
            }
        }

        // Function to show stream details
        function showStreamDetails(index) {
            // Implement stream details modal or navigation
            window.location.href = `/view/stream/${index}`;
        }

        // Function to update stream status in UI
        function updateStreamStatus(data) {
            if (!data.stream_statuses) return;

            Object.entries(data.stream_statuses).forEach(([index, status]) => {
                const cell = document.querySelector(`.preview-cell:nth-child(${parseInt(index) + 1})`);
                if (!cell) return;

                if (status.is_active) {
                    cell.classList.add('active');
                    cell.classList.remove('error');
                } else {
                    cell.classList.remove('active');
                    cell.classList.add('error');
                }

                const indicator = cell.querySelector('.status-indicator');
                if (indicator) {
                    indicator.classList.toggle('offline', !status.is_active);
                }
            });
        }

        // Function to log messages
        function logMessage(message, type = 'info') {
            const errorLog = document.getElementById('errorLog');
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'error-entry';
            entry.textContent = `[${timestamp}] ${message}`;
            errorLog.insertBefore(entry, errorLog.firstChild);
        }

        // Function to clear error log
        function clearErrorLog() {
            const errorLog = document.getElementById('errorLog');
            errorLog.innerHTML = '';
        }

        // Start periodic status check
        setInterval(checkAllStreams, 30000); // Check every 30 seconds
    </script>
</body>
</html> 