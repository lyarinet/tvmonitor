@extends('layouts.guide')

@section('title', 'Stream Health Monitoring')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Stream Health Monitoring</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            This guide explains how to monitor the health and status of your streams in real-time using the TV Monitor System. Effective monitoring ensures you can quickly identify and address any issues with your video feeds.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">The Stream Health Dashboard</h2>
        <p class="mb-4">
            The Stream Health Dashboard provides a centralized view of all your stream statuses:
        </p>
        <ol class="list-decimal list-inside mb-4 space-y-2">
            <li>Navigate to the <strong>Stream Health</strong> section in the main menu.</li>
            <li>The dashboard displays all your streams with real-time status indicators.</li>
            <li>Streams are categorized by type (input streams, output streams) for easy monitoring.</li>
            <li>Each stream shows key health metrics and a status indicator.</li>
        </ol>
        
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
            <h3 class="font-semibold mb-2">Understanding Status Indicators</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="flex items-center mb-1">
                        <span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                        <strong>Green (Healthy)</strong>
                    </p>
                    <p class="text-sm ml-5">Stream is active and functioning normally</p>
                </div>
                <div>
                    <p class="flex items-center mb-1">
                        <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                        <strong>Yellow (Warning)</strong>
                    </p>
                    <p class="text-sm ml-5">Stream has performance issues or warnings</p>
                </div>
                <div>
                    <p class="flex items-center mb-1">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                        <strong>Red (Error)</strong>
                    </p>
                    <p class="text-sm ml-5">Stream is down or has critical errors</p>
                </div>
                <div>
                    <p class="flex items-center mb-1">
                        <span class="inline-block w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                        <strong>Gray (Inactive)</strong>
                    </p>
                    <p class="text-sm ml-5">Stream is disabled or not configured</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Key Health Metrics</h2>
        <p class="mb-4">
            The system monitors several key metrics for each stream:
        </p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 shadow-sm rounded-lg">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Metric</th>
                        <th class="py-2 px-4 border-b text-left">Description</th>
                        <th class="py-2 px-4 border-b text-left">Normal Range</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 px-4 border-b font-medium">Uptime</td>
                        <td class="py-2 px-4 border-b">How long the stream has been running continuously</td>
                        <td class="py-2 px-4 border-b">Varies by use case</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b font-medium">Bitrate</td>
                        <td class="py-2 px-4 border-b">Data rate of the stream in Mbps</td>
                        <td class="py-2 px-4 border-b">1-15 Mbps (HD), 5-25 Mbps (4K)</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b font-medium">Frame Rate</td>
                        <td class="py-2 px-4 border-b">Frames per second being delivered</td>
                        <td class="py-2 px-4 border-b">24-60 fps</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b font-medium">Buffer Health</td>
                        <td class="py-2 px-4 border-b">Current buffer status in seconds</td>
                        <td class="py-2 px-4 border-b">3-10 seconds</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b font-medium">Packet Loss</td>
                        <td class="py-2 px-4 border-b">Percentage of data packets lost during transmission</td>
                        <td class="py-2 px-4 border-b">< 0.5%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="mt-4 text-sm text-gray-700">
            <strong>Note:</strong> Acceptable ranges may vary based on your specific requirements and network conditions.
        </p>
    </div>

    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-500 p-4">
        <h3 class="text-lg font-semibold text-yellow-800">Alerts and Notifications</h3>
        <p class="mb-2">
            The system can send alerts when stream health issues are detected:
        </p>
        <ol class="list-decimal list-inside">
            <li class="mb-2">Go to <strong>Settings</strong> > <strong>Notifications</strong></li>
            <li class="mb-2">Configure your preferred notification methods:
                <ul class="list-disc list-inside ml-6 mt-1">
                    <li>Email notifications</li>
                    <li>SMS alerts (requires configuration)</li>
                    <li>Webhook notifications</li>
                    <li>On-screen alerts</li>
                </ul>
            </li>
            <li>Set thresholds for different alert levels (warning, error, critical)</li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Detailed Stream Analysis</h2>
        <p class="mb-4">
            For in-depth analysis of a specific stream:
        </p>
        <ol class="list-decimal list-inside mb-4 space-y-2">
            <li>Click on any stream in the health dashboard to open its detailed view.</li>
            <li>The detailed view provides:
                <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                    <li><strong>Real-time Metrics:</strong> Continuously updated graphs of bitrate, frame rate, etc.</li>
                    <li><strong>Stream Information:</strong> Resolution, codec, audio channels, etc.</li>
                    <li><strong>Error Log:</strong> Recent errors or issues with the stream</li>
                    <li><strong>Thumbnail Preview:</strong> Visual confirmation of the stream content</li>
                </ul>
            </li>
            <li>Use the timeline view to review historical data and identify patterns.</li>
        </ol>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Bitrate Analysis</h3>
                <p class="text-sm mb-2">The bitrate graph shows data rate over time:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Consistent line: Stable stream</li>
                    <li>Fluctuations: Variable content or network issues</li>
                    <li>Sudden drops: Potential connection problems</li>
                    <li>Gradual decline: Possible bandwidth throttling</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Frame Rate Analysis</h3>
                <p class="text-sm mb-2">The frame rate graph shows delivery performance:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Steady at expected value: Healthy stream</li>
                    <li>Fluctuating: Processing or delivery issues</li>
                    <li>Below target: Encoder or network problems</li>
                    <li>Irregular pattern: Possible encoding configuration issue</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Common Issues and Solutions</h2>
        
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Buffering Issues</h3>
                <p class="text-sm mb-2"><strong>Symptoms:</strong> Stream stops and starts, playback is not smooth</p>
                <p class="text-sm mb-2"><strong>Possible Causes:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Insufficient bandwidth</li>
                    <li>Network congestion</li>
                    <li>Server overload</li>
                </ul>
                <p class="text-sm mb-2"><strong>Solutions:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Decrease stream bitrate</li>
                    <li>Increase server resources</li>
                    <li>Check network path for bottlenecks</li>
                    <li>Implement a CDN for distribution</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Stream Not Available</h3>
                <p class="text-sm mb-2"><strong>Symptoms:</strong> Stream shows error or is completely unavailable</p>
                <p class="text-sm mb-2"><strong>Possible Causes:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Source disconnection</li>
                    <li>Invalid stream URL</li>
                    <li>Authentication failure</li>
                    <li>Server crash</li>
                </ul>
                <p class="text-sm mb-2"><strong>Solutions:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Verify source is broadcasting</li>
                    <li>Check stream URL and credentials</li>
                    <li>Restart source encoder</li>
                    <li>Check server logs for specific errors</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Quality Degradation</h3>
                <p class="text-sm mb-2"><strong>Symptoms:</strong> Poor image quality, artifacts, or pixelation</p>
                <p class="text-sm mb-2"><strong>Possible Causes:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Insufficient bitrate for content type</li>
                    <li>Encoder configuration issues</li>
                    <li>Source quality problems</li>
                </ul>
                <p class="text-sm mb-2"><strong>Solutions:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Increase bitrate if bandwidth allows</li>
                    <li>Optimize encoder settings</li>
                    <li>Check source quality before encoding</li>
                    <li>Review codec and profile settings</li>
                </ul>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">HLS Streaming Issues</h3>
                <p class="text-sm mb-2"><strong>Symptoms:</strong> HLS.js errors like "fragParsingError" or "fragLoadError", blank player with network errors</p>
                <p class="text-sm mb-2"><strong>Possible Causes:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Missing or incorrect segment URL formatting</li>
                    <li>HLS playlist version incompatibility</li>
                    <li>Missing critical HLS tags in playlist</li>
                    <li>Segment file access issues</li>
                </ul>
                <p class="text-sm mb-2"><strong>Solutions:</strong></p>
                <ul class="list-disc list-inside text-sm">
                    <li>Ensure segment URLs use correct format: /stream-proxy/{streamId}/segment/segment_{number}.ts</li>
                    <li>Use the system's direct playlist access which preserves original HLS playlist tags</li>
                    <li>Check browser console for specific HLS.js error messages</li>
                    <li>Verify segment files are accessible through direct URL testing</li>
                    <li>Try using the test player at /test-player.html to isolate player vs. system issues</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Advanced Monitoring Tools</h2>
        <p class="mb-4">
            The TV Monitor System provides several advanced tools for deeper stream analysis:
        </p>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-semibold mb-2">Stream Inspector</h3>
                <p class="mb-2">
                    The Stream Inspector provides detailed technical information about stream composition:
                </p>
                <ul class="list-disc list-inside">
                    <li><strong>Video Analysis:</strong> Codec details, color space, aspect ratio, etc.</li>
                    <li><strong>Audio Analysis:</strong> Sample rate, channels, bitrate, format, etc.</li>
                    <li><strong>Metadata Analysis:</strong> Stream tags, identifiers, and custom fields</li>
                </ul>
                <p class="text-sm mt-2">
                    Access Stream Inspector by clicking the "Inspect" button in the detailed stream view.
                </p>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold mb-2">Network Analyzer</h3>
                <p class="mb-2">
                    The Network Analyzer helps identify transmission issues:
                </p>
                <ul class="list-disc list-inside">
                    <li><strong>Packet Analysis:</strong> Examine network packet delivery patterns</li>
                    <li><strong>Latency Testing:</strong> Measure delivery time from source to destination</li>
                    <li><strong>Route Tracing:</strong> Identify network path bottlenecks</li>
                </ul>
                <p class="text-sm mt-2">
                    Access Network Analyzer from the "Tools" dropdown in the stream health dashboard.
                </p>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold mb-2">Historical Reporting</h3>
                <p class="mb-2">
                    Generate reports to analyze stream performance over time:
                </p>
                <ul class="list-disc list-inside">
                    <li><strong>Availability Reports:</strong> Uptime and reliability statistics</li>
                    <li><strong>Performance Reports:</strong> Quality and performance metrics over time</li>
                    <li><strong>Issue Reports:</strong> Compilation of errors and their frequency</li>
                </ul>
                <p class="text-sm mt-2">
                    Access Reports by clicking the "Reports" button in the stream health dashboard.
                </p>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Best Practices</h2>
        <p class="mb-4">
            Follow these recommendations for effective stream health monitoring:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>Regular Checks:</strong> Make stream health monitoring part of your regular workflow.</li>
            <li><strong>Baseline Establishment:</strong> Document normal performance metrics for each stream type as a reference point.</li>
            <li><strong>Proactive Monitoring:</strong> Configure alerts to catch issues before they affect viewers.</li>
            <li><strong>Redundancy Planning:</strong> Have backup sources configured for critical streams.</li>
            <li><strong>Performance Tuning:</strong> Regularly review performance metrics and adjust encoding parameters as needed.</li>
            <li><strong>Documentation:</strong> Keep records of issues and resolutions to identify patterns and improve response time.</li>
        </ul>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Related Guides</h2>
        <p class="mb-4">
            For more information on related topics, see these guides:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><a href="{{ route('guides.show', 'input-stream-guide') }}" class="text-blue-600 hover:underline">Input Stream Management Guide</a> - Learn how to configure and manage input video streams.</li>
            <li><a href="{{ route('guides.show', 'output-stream-guide') }}" class="text-blue-600 hover:underline">Output Stream Configuration Guide</a> - Configure and manage output streams.</li>
            <li><a href="{{ route('guides.show', 'multiview-layout-guide') }}" class="text-blue-600 hover:underline">Multiview Layout Configuration</a> - Design custom layouts for monitoring multiple streams.</li>
        </ul>
    </div>
</div>
@endsection 