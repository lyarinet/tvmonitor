@extends('layouts.guide')

@section('title', 'Stream Management Guide')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Stream Management Guide</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            This guide will walk you through the process of setting up, starting, and managing streams in the TV Monitor System.
            The system handles both input streams (sources) and output streams (multiview compositions).
        </p>
    </div>

    <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4">
        <h3 class="text-lg font-semibold text-blue-800">Quick Start Checklist</h3>
        <ol class="list-decimal pl-6 mt-2 space-y-1">
            <li>Configure input streams (your source videos)</li>
            <li>Create a multiview layout</li>
            <li>Add layout positions and assign input streams</li>
            <li>Configure output stream settings</li>
            <li>Start the stream</li>
            <li>Monitor stream health</li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="input-streams">1. Setting Up Input Streams</h2>
        <p class="mb-4">
            Input streams are your source videos that will be composed into a multiview layout.
        </p>
        
        <h3 class="text-xl font-medium mb-3">To configure an input stream:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Input Streams</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>New Input Stream</strong>.</p>
            </li>
            <li>
                <p class="mb-2">Fill in the required information:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Name:</strong> A descriptive name for your stream (e.g., "Camera 1")</li>
                    <li><strong>URL/Source:</strong> The source URL of your stream (e.g., RTMP, HLS, UDP, or file path)</li>
                    <li><strong>Stream Type:</strong> Select the appropriate protocol (RTMP, HLS, UDP, etc.)</li>
                    <li><strong>Status:</strong> Set to "Active" to enable the stream</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Configure advanced options if needed:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Authentication:</strong> Username/password if required</li>
                    <li><strong>Low Latency:</strong> Enable for reduced delay</li>
                    <li><strong>Reconnect Attempts:</strong> Number of times to try reconnecting if the stream fails</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Click <strong>Create Input Stream</strong> to save.</p>
            </li>
        </ol>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
            <p class="text-yellow-700">
                <strong>Note:</strong> The system will automatically attempt to connect to active input streams. You can view the connection status on the Input Streams list page.
            </p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="multiview-layouts">2. Creating a Multiview Layout</h2>
        <p class="mb-4">
            Multiview layouts define how your input streams will be arranged in the final output.
        </p>
        
        <h3 class="text-xl font-medium mb-3">To create a multiview layout:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Multiview Layouts</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>New Multiview Layout</strong>.</p>
            </li>
            <li>
                <p class="mb-2">Configure the basic layout settings:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Name:</strong> A descriptive name for your layout</li>
                    <li><strong>Rows/Columns:</strong> Define the grid structure (e.g., 2x2 for four equal positions)</li>
                    <li><strong>Width/Height:</strong> The resolution of the layout (e.g., 1920x1080)</li>
                    <li><strong>Background Color:</strong> The color to display behind the streams</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Click <strong>Create Multiview Layout</strong> to save.</p>
            </li>
            <li>
                <p class="mb-2">After creating the layout, you'll be redirected to the edit page where you can:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li>Click <strong>Generate Grid Layout</strong> to automatically create positions based on your rows/columns</li>
                    <li>Or manually add positions using the <strong>Add Position</strong> button</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="layout-positions">3. Configuring Layout Positions</h2>
        <p class="mb-4">
            Layout positions define where each input stream appears in your multiview layout.
        </p>
        
        <h3 class="text-xl font-medium mb-3">If you used "Generate Grid Layout":</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Scroll down to the <strong>Layout Positions</strong> section on the edit page.</p>
            </li>
            <li>
                <p class="mb-2">For each position, click the edit (pencil) icon.</p>
            </li>
            <li>
                <p class="mb-2">Assign an input stream to the position using the dropdown menu.</p>
            </li>
            <li>
                <p class="mb-2">Adjust other settings as needed (label position, z-index, etc.).</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>Save</strong> to update the position.</p>
            </li>
        </ol>
        
        <h3 class="text-xl font-medium mb-3 mt-6">To manually add a position:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Click <strong>Add Position</strong> on the multiview layout edit page.</p>
            </li>
            <li>
                <p class="mb-2">Fill in the position details:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Input Stream:</strong> Select the stream to display in this position</li>
                    <li><strong>X/Y Position:</strong> The coordinates within the layout (0,0 is top-left)</li>
                    <li><strong>Width/Height:</strong> The size of this position in pixels</li>
                    <li><strong>Z-Index:</strong> Controls which stream appears on top when positions overlap</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Click <strong>Create Layout Position</strong> to save.</p>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="output-streams">4. Setting Up Output Streams</h2>
        <p class="mb-4">
            Output streams take your multiview layout and broadcast it to a destination.
        </p>
        
        <h3 class="text-xl font-medium mb-3">To configure an output stream:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Output Streams</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>New Output Stream</strong>.</p>
            </li>
            <li>
                <p class="mb-2">Fill in the required information:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Name:</strong> A descriptive name for your output</li>
                    <li><strong>Multiview Layout:</strong> Select the layout you created earlier</li>
                    <li><strong>Output Format:</strong> The streaming protocol (RTMP, HLS, UDP, etc.)</li>
                    <li><strong>Destination URL:</strong> Where to send the stream</li>
                    <li><strong>Status:</strong> Set to "Active" to enable the stream</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Configure encoding settings:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Video Codec:</strong> Usually H.264 for compatibility</li>
                    <li><strong>Video Bitrate:</strong> Higher values give better quality but require more bandwidth</li>
                    <li><strong>Audio Source:</strong> Which input stream to use for audio (if any)</li>
                    <li><strong>Audio Codec/Bitrate:</strong> Configure audio encoding settings</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Click <strong>Create Output Stream</strong> to save.</p>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="starting-streams">5. Starting the Stream</h2>
        <p class="mb-4">
            Once you've configured your input streams, multiview layout, and output stream, you're ready to start streaming.
        </p>
        
        <h3 class="text-xl font-medium mb-3">To start a stream:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Output Streams</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Find your output stream in the list.</p>
            </li>
            <li>
                <p class="mb-2">If it's not already active, click the edit (pencil) icon.</p>
            </li>
            <li>
                <p class="mb-2">Change the <strong>Status</strong> to "Active".</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>Save Changes</strong>.</p>
            </li>
            <li>
                <p class="mb-2">The system will automatically:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li>Connect to all required input streams</li>
                    <li>Compose them according to your multiview layout</li>
                    <li>Start the FFmpeg process to generate the output stream</li>
                    <li>Begin sending the stream to your specified destination</li>
                </ul>
            </li>
        </ol>
        
        <div class="bg-green-50 border-l-4 border-green-500 p-4 my-4">
            <p class="text-green-700">
                <strong>Tip:</strong> You can also use the quick actions on the Output Streams list page to start/stop streams without entering the edit page.
            </p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="stopping-streams">6. Stopping a Stream</h2>
        <p class="mb-4">
            There are several ways to stop a running stream when needed.
        </p>
        
        <h3 class="text-xl font-medium mb-3">Method 1: Using Quick Actions</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Output Streams</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Find your active output stream in the list.</p>
            </li>
            <li>
                <p class="mb-2">Click the <strong>Stop Stream</strong> button in the actions column.</p>
            </li>
            <li>
                <p class="mb-2">Confirm the action when prompted.</p>
            </li>
        </ol>
        
        <h3 class="text-xl font-medium mb-3 mt-6">Method 2: Editing Stream Status</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Output Streams</strong> section in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Click the edit (pencil) icon for your active stream.</p>
            </li>
            <li>
                <p class="mb-2">Change the <strong>Status</strong> to "Inactive".</p>
            </li>
            <li>
                <p class="mb-2">Click <strong>Save Changes</strong>.</p>
            </li>
        </ol>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
            <p class="text-yellow-700">
                <strong>Important:</strong> When you stop a stream, the system will:
                <ul class="list-disc pl-6 mt-2">
                    <li>Terminate the FFmpeg process</li>
                    <li>Release resources associated with the output stream</li>
                    <li>Stop sending data to the destination</li>
                    <li>Keep input streams running if they're used by other outputs</li>
                </ul>
            </p>
        </div>
        
        <h3 class="text-xl font-medium mb-3 mt-6">Emergency Stop</h3>
        <p class="mb-4">
            If you need to stop all streams immediately (for example, in case of resource overload):
        </p>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Dashboard</strong> in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">Look for the <strong>System Controls</strong> widget.</p>
            </li>
            <li>
                <p class="mb-2">Click the <strong>Emergency Stop All Streams</strong> button.</p>
            </li>
            <li>
                <p class="mb-2">Confirm the action when prompted.</p>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="monitoring">7. Monitoring Stream Health</h2>
        <p class="mb-4">
            Once your streams are running, it's important to monitor their health and performance.
        </p>
        
        <h3 class="text-xl font-medium mb-3">To monitor your streams:</h3>
        <ol class="list-decimal pl-6 space-y-3">
            <li>
                <p class="mb-2">Navigate to the <strong>Dashboard</strong> in the admin panel.</p>
            </li>
            <li>
                <p class="mb-2">View the <strong>Stream Health</strong> widget for a quick overview of all streams.</p>
            </li>
            <li>
                <p class="mb-2">Check the status indicators:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li><strong>Green:</strong> Stream is healthy</li>
                    <li><strong>Yellow:</strong> Stream has minor issues (e.g., occasional frame drops)</li>
                    <li><strong>Red:</strong> Stream is failing or has major issues</li>
                </ul>
            </li>
            <li>
                <p class="mb-2">Click on a specific stream to view detailed metrics:</p>
                <ul class="list-disc pl-6 mb-2">
                    <li>Current bitrate</li>
                    <li>Frame rate</li>
                    <li>Buffer health</li>
                    <li>Error logs</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="troubleshooting">Troubleshooting Common Issues</h2>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">Input Stream Not Connecting</h3>
            <ul class="list-disc pl-6">
                <li>Verify the source URL is correct and accessible</li>
                <li>Check if authentication credentials are required</li>
                <li>Ensure the source is actually streaming content</li>
                <li>Check network connectivity between the server and source</li>
                <li>Review the logs for specific error messages</li>
            </ul>
        </div>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">Output Stream Not Starting</h3>
            <ul class="list-disc pl-6">
                <li>Verify all required input streams are connected and healthy</li>
                <li>Check that the multiview layout has valid positions configured</li>
                <li>Ensure the destination URL/server is accessible</li>
                <li>Check if the destination requires authentication</li>
                <li>Review the FFmpeg logs for encoding or streaming errors</li>
            </ul>
        </div>
        
        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">Poor Stream Quality</h3>
            <ul class="list-disc pl-6">
                <li>Increase the video bitrate for better quality</li>
                <li>Check if input streams have sufficient quality</li>
                <li>Ensure server has adequate CPU resources for encoding</li>
                <li>Try a different encoding preset (slower presets generally produce better quality)</li>
                <li>Verify network bandwidth is sufficient for the configured bitrate</li>
            </ul>
        </div>

        <div class="mb-4">
            <h3 class="text-xl font-medium mb-2">HLS Streaming Issues</h3>
            <ul class="list-disc pl-6">
                <li>The system now preserves original HLS playlist properties like <code>#EXT-X-VERSION</code> and <code>#EXT-X-INDEPENDENT-SEGMENTS</code> tags</li>
                <li>If experiencing <code>fragLoadError</code> issues with HLS.js, ensure you're using the latest version of the system which fixes segment URL path formatting</li>
                <li>HLS segment URLs are now properly formatted with the correct path separator (e.g., <code>/stream-proxy/14/segment/segment_123.ts</code>)</li>
                <li>For maximum compatibility with players, the system maintains the original playlist format when proxying HLS streams</li>
                <li>Check browser console for HLS.js errors if streams aren't playing properly</li>
            </ul>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="best-practices">Best Practices</h2>
        <ul class="list-disc pl-6 space-y-2">
            <li>Start with lower bitrates and increase as needed to find the optimal balance between quality and performance</li>
            <li>Regularly check the health of your streams, especially after making configuration changes</li>
            <li>Set up monitoring alerts to be notified of stream issues</li>
            <li>Consider creating backup output streams with different destinations for redundancy</li>
            <li>Periodically restart long-running streams to prevent resource leaks</li>
            <li>Keep FFmpeg updated to benefit from performance improvements and bug fixes</li>
            <li>For HLS streams, prefer using the original playlist when possible rather than generating a new one</li>
            <li>Test streams with multiple players (VLC, browser-based HLS.js, native players) to ensure broad compatibility</li>
        </ul>
    </div>

    <div class="mt-12 pt-6 border-t border-gray-200">
        <h2 class="text-2xl font-semibold mb-4">Need More Help?</h2>
        <p>
            If you encounter issues not covered in this guide, please contact your system administrator or refer to the
            <a href="{{ route('guides.index') }}" class="text-blue-600 hover:underline">other available guides</a>.
        </p>
    </div>
</div>
@endsection 