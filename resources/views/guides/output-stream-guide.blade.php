@extends('layouts.guide')

@section('title', 'Output Stream Configuration Guide')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Output Stream Configuration Guide</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            Output streams are the final product of your multiview layout. They take the configured layout with its input streams
            and generate a new stream that can be distributed to viewers or recording systems.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Creating an Output Stream</h2>
        <ol class="list-decimal pl-6 space-y-4">
            <li>
                <p class="mb-2"><strong>Navigate to Output Streams:</strong> From the admin panel, click on "Output Streams" in the navigation menu.</p>
            </li>
            <li>
                <p class="mb-2"><strong>Create New:</strong> Click the "New Output Stream" button.</p>
            </li>
            <li>
                <p class="mb-2"><strong>Basic Information:</strong> Fill in the required fields:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Name:</strong> A descriptive name for your output stream (e.g., "Main Control Room Multiview")</li>
                    <li><strong>Description:</strong> Optional details about the purpose or content of this output</li>
                </ul>
            </li>
            <li>
                <p class="mb-2"><strong>Select Multiview Layout:</strong> Choose the layout that will be used for this output stream. This determines the arrangement of input streams.</p>
            </li>
            <li>
                <p class="mb-2"><strong>Output Configuration:</strong> Configure the output settings:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Format:</strong> The container format for the output (e.g., RTMP, HLS, UDP)</li>
                    <li><strong>URL/Destination:</strong> Where the stream will be sent:
                        <ul class="list-disc pl-6 mt-2 text-sm">
                            <li><strong>RTMP:</strong> Use format <code>rtmp://server-address/application/stream-key</code> (e.g., <code>rtmp://live.example.com/live/stream1</code>)</li>
                            <li><strong>RTSP:</strong> Use format <code>rtsp://ip-address:port/stream-name</code> (e.g., <code>rtsp://192.168.1.100:8554/stream1</code>)</li>
                            <li><strong>HLS:</strong> Local path for HLS segments (e.g., <code>/var/www/html/streams/output1</code> or <code>storage/app/public/streams/output1</code>)</li>
                            <li><strong>DASH:</strong> Local path for DASH segments (e.g., <code>/var/www/html/dash/output1</code>) with manifest file (<code>manifest.mpd</code>)</li>
                            <li><strong>HTTP:</strong> Use format <code>http://server-address/path</code> for HTTP POST streaming (e.g., <code>http://streaming.example.com/ingest/stream1</code>)</li>
                            <li><strong>UDP:</strong> Use format <code>udp://ip-address:port</code> (e.g., <code>udp://10.0.0.1:1234</code> or <code>udp://239.0.0.1:1234</code> for multicast)</li>
                            <li><strong>SRT:</strong> Use format <code>srt://ip-address:port</code> with optional parameters (e.g., <code>srt://192.168.1.10:9000?pkt_size=1316&mode=caller</code>)</li>
                            <li><strong>File:</strong> Local path for recording (e.g., <code>storage/app/recordings/output1.mp4</code>)</li>
                            <li><strong>Storage Paths:</strong> Use <code>{storage_path}</code> placeholder for app storage (e.g., <code>{storage_path}/streams/{id}/playlist.m3u8</code>). The <code>{id}</code> placeholder will be replaced with the stream ID.</li>
                        </ul>
                    </li>
                    <li><strong>Resolution:</strong> The output resolution (defaults to the layout's resolution)</li>
                    <li><strong>Bitrate:</strong> The target bitrate for the output stream</li>
                    <li><strong>Framerate:</strong> The target frames per second</li>
                </ul>
            </li>
            <li>
                <p class="mb-2"><strong>Advanced Options:</strong> Configure additional settings if needed:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Audio Source:</strong> Which input stream's audio to use (if any)</li>
                    <li><strong>Audio Bitrate:</strong> The target audio bitrate</li>
                    <li><strong>Encoding Preset:</strong> Balance between encoding speed and quality</li>
                    <li><strong>Additional FFmpeg Options:</strong> Custom parameters for advanced users</li>
                </ul>
            </li>
            <li>
                <p class="mb-2"><strong>Save:</strong> Click "Create Output Stream" to save your configuration.</p>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Editing an Output Stream</h2>
        <ol class="list-decimal pl-6 space-y-4">
            <li>
                <p class="mb-2"><strong>Navigate to Output Streams:</strong> From the admin panel, click on "Output Streams" in the navigation menu.</p>
            </li>
            <li>
                <p class="mb-2"><strong>Select Stream:</strong> Find the output stream you want to edit and click the edit (pencil) icon.</p>
            </li>
            <li>
                <p class="mb-2"><strong>Modify Settings:</strong> Update any of the settings as needed.</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <p class="text-yellow-700">
                        <strong>Important:</strong> Changing the multiview layout may affect how your output stream appears. Ensure that the new layout is compatible with your output requirements.
                    </p>
                </div>
            </li>
            <li>
                <p class="mb-2"><strong>Save Changes:</strong> Click "Save Changes" to update your output stream configuration.</p>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Monitoring Output Streams</h2>
        <p class="mb-4">
            Once your output stream is configured, you can monitor its status from the dashboard:
        </p>
        <ul class="list-disc pl-6 space-y-2">
            <li><strong>Status Indicator:</strong> Shows if the stream is active, inactive, or experiencing issues</li>
            <li><strong>Thumbnail Preview:</strong> A visual preview of the current output</li>
            <li><strong>Health Metrics:</strong> Information about bitrate, frame rate, and other technical parameters</li>
            <li><strong>Logs:</strong> Access to FFmpeg logs for troubleshooting</li>
        </ul>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Troubleshooting Common Issues</h2>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">Stream Not Starting</h3>
            <ul class="list-disc pl-6">
                <li>Verify that the multiview layout exists and has valid layout positions</li>
                <li>Check that all required input streams in the layout are active</li>
                <li>Ensure the destination URL/address is correct and accessible</li>
                <li>Review FFmpeg logs for specific error messages</li>
            </ul>
        </div>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">Poor Quality Output</h3>
            <ul class="list-disc pl-6">
                <li>Increase the bitrate for higher quality (at the cost of bandwidth)</li>
                <li>Check that input streams have sufficient quality</li>
                <li>Try a different encoding preset (slower presets generally produce better quality)</li>
                <li>Ensure the output resolution is appropriate for your needs</li>
            </ul>
        </div>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">High CPU Usage</h3>
            <ul class="list-disc pl-6">
                <li>Lower the output resolution or framerate</li>
                <li>Use a faster encoding preset</li>
                <li>Reduce the number of input streams in your layout</li>
                <li>Consider hardware acceleration if available</li>
            </ul>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Best Practices</h2>
        <ul class="list-disc pl-6 space-y-2">
            <li>Start with lower bitrates and increase as needed to find the optimal balance between quality and bandwidth</li>
            <li>Use descriptive names for your output streams to easily identify them</li>
            <li>Regularly check the health of your output streams</li>
            <li>Consider creating backup output streams with different destinations for redundancy</li>
            <li>Test your output streams with different viewers/players to ensure compatibility</li>
        </ul>
    </div>
</div>
@endsection 