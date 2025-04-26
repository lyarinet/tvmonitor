@extends('layouts.guide')

@section('title', 'Demo Data Guide')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Demo Data Guide</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            This guide explains the demo data included with the TV Monitor System. The demo data provides realistic examples
            of input streams, multiview layouts, and output streams to help you understand how the system works.
        </p>
    </div>

    <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4">
        <h3 class="text-lg font-semibold text-blue-800">Installing Demo Data</h3>
        <p class="mb-2">To install the demo data, run the following command from your terminal:</p>
        <div class="bg-gray-800 text-white p-3 rounded font-mono text-sm mb-2">
            php artisan tvmonitor:install-demo
        </div>
        <p class="text-sm text-blue-700">
            Note: This will add demo data to your database. It will not delete existing data.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="input-streams">1. Demo Input Streams</h2>
        <p class="mb-4">
            The demo data includes 8 input streams representing typical sources in a broadcast environment:
        </p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 mb-4">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-2 px-4 text-sm">News Studio Camera 1</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/studio1</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Main camera for the news studio</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">News Studio Camera 2</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/studio2</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Wide angle camera for the news studio</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Weather Graphics</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/weather</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Weather graphics system output</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Sports Feed</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">udp://239.0.0.1:1234</td>
                        <td class="py-2 px-4 text-sm">UDP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Live sports feed from satellite</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">International News Feed</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">udp://239.0.0.2:1234</td>
                        <td class="py-2 px-4 text-sm">UDP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">International news feed</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Studio Clock</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/clock</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Studio clock and timer</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Backup Camera</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/backup</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span></td>
                        <td class="py-2 px-4 text-sm">Backup camera for emergencies</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Test Pattern</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://demo.server.com/live/testpattern</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                        <td class="py-2 px-4 text-sm">Color bars test pattern</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
            <p class="text-yellow-700">
                <strong>Note:</strong> These are demo URLs and won't actually connect to real streams. In a production environment,
                you would replace these with real stream URLs from your cameras, encoders, or other sources.
            </p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="multiview-layouts">2. Demo Multiview Layouts</h2>
        <p class="mb-4">
            The demo data includes two multiview layouts:
        </p>
        
        <h3 class="text-xl font-medium mb-3">Standard 2x2 Grid</h3>
        <div class="mb-6">
            <div class="bg-black p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 aspect-video">
                    <div class="border border-white relative">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">News Studio Camera 1</div>
                    </div>
                    <div class="border border-white relative">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">News Studio Camera 2</div>
                    </div>
                    <div class="border border-white relative">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Weather Graphics</div>
                    </div>
                    <div class="border border-white relative">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Sports Feed</div>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-600">
                A simple 2x2 grid layout with equal-sized cells, commonly used for basic monitoring.
            </p>
        </div>
        
        <h3 class="text-xl font-medium mb-3">Program & Preview</h3>
        <div class="mb-6">
            <div class="bg-black p-4 rounded-lg mb-4">
                <div class="flex flex-col aspect-video">
                    <!-- Program (top half) -->
                    <div class="border-2 border-red-500 h-1/2 mb-1 relative">
                        <div class="absolute top-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">PROGRAM - News Studio Camera 1</div>
                    </div>
                    
                    <!-- Middle row (preview and source 1) -->
                    <div class="flex h-1/4 mb-1">
                        <div class="border-2 border-green-500 w-1/2 mr-1 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">PREVIEW - News Studio Camera 2</div>
                        </div>
                        <div class="border border-white w-1/2 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Weather Graphics</div>
                        </div>
                    </div>
                    
                    <!-- Bottom row (4 sources) -->
                    <div class="flex h-1/4">
                        <div class="border border-white w-1/4 mr-1 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Sports Feed</div>
                        </div>
                        <div class="border border-white w-1/4 mr-1 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Int'l News</div>
                        </div>
                        <div class="border border-white w-1/4 mr-1 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Studio Clock</div>
                        </div>
                        <div class="border border-white w-1/4 relative">
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">Test Pattern</div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-600">
                A production-oriented layout with a large program window, preview window, and smaller source monitors.
                Program and preview windows have colored borders for easy identification.
            </p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="output-streams">3. Demo Output Streams</h2>
        <p class="mb-4">
            The demo data includes three output streams configured for different purposes:
        </p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 mb-4">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layout</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-2 px-4 text-sm">Control Room Multiview</td>
                        <td class="py-2 px-4 text-sm">Standard 2x2 Grid</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://streaming.local/multiview/control_room</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Director Multiview</td>
                        <td class="py-2 px-4 text-sm">Program & Preview</td>
                        <td class="py-2 px-4 text-sm">RTMP</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">rtmp://streaming.local/multiview/director</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span></td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 text-sm">Recording Multiview</td>
                        <td class="py-2 px-4 text-sm">Standard 2x2 Grid</td>
                        <td class="py-2 px-4 text-sm">File</td>
                        <td class="py-2 px-4 text-sm font-mono text-xs">/var/media/recordings/multiview_%Y%m%d_%H%M%S.mp4</td>
                        <td class="py-2 px-4 text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <h3 class="text-xl font-medium mb-3">Output Stream Details</h3>
        
        <div class="mb-6">
            <h4 class="text-lg font-medium mb-2">Control Room Multiview</h4>
            <ul class="list-disc pl-6 space-y-1 text-sm">
                <li><strong>Purpose:</strong> For display on monitors in the control room</li>
                <li><strong>Layout:</strong> Standard 2x2 grid showing four key sources</li>
                <li><strong>Video:</strong> H.264, 5 Mbps, 1920x1080, 30 fps</li>
                <li><strong>Audio:</strong> AAC, 128 kbps from News Studio Camera 1</li>
                <li><strong>Encoding Preset:</strong> veryfast (low CPU usage)</li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h4 class="text-lg font-medium mb-2">Director Multiview</h4>
            <ul class="list-disc pl-6 space-y-1 text-sm">
                <li><strong>Purpose:</strong> For the director's monitor with program/preview focus</li>
                <li><strong>Layout:</strong> Program & Preview with additional sources</li>
                <li><strong>Video:</strong> H.264, 8 Mbps, 1920x1080, 60 fps</li>
                <li><strong>Audio:</strong> AAC, 192 kbps from News Studio Camera 1</li>
                <li><strong>Encoding Preset:</strong> medium (balanced quality/CPU usage)</li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h4 class="text-lg font-medium mb-2">Recording Multiview</h4>
            <ul class="list-disc pl-6 space-y-1 text-sm">
                <li><strong>Purpose:</strong> For archiving the multiview output to file</li>
                <li><strong>Layout:</strong> Standard 2x2 grid</li>
                <li><strong>Video:</strong> H.264, 10 Mbps, 1920x1080, 30 fps</li>
                <li><strong>Audio:</strong> AAC, 256 kbps from News Studio Camera 1</li>
                <li><strong>Encoding Preset:</strong> slow (higher quality)</li>
                <li><strong>Additional:</strong> 1-hour segments for easier file management</li>
                <li><strong>Status:</strong> Inactive (can be activated when recording is needed)</li>
            </ul>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="using-demo-data">4. Using the Demo Data</h2>
        
        <p class="mb-4">
            The demo data provides a realistic starting point for exploring the TV Monitor System. Here are some ways to use it:
        </p>
        
        <h3 class="text-xl font-medium mb-3">Exploring the Admin Interface</h3>
        <ul class="list-disc pl-6 space-y-2 mb-4">
            <li>Browse through the input streams, multiview layouts, and output streams to see how they're configured</li>
            <li>Edit an existing layout to see how changes affect the preview</li>
            <li>Try activating the inactive "Recording Multiview" output stream</li>
            <li>Experiment with the "Generate Grid Layout" feature on a multiview layout</li>
        </ul>
        
        <h3 class="text-xl font-medium mb-3">Creating Your Own Configurations</h3>
        <ul class="list-disc pl-6 space-y-2 mb-4">
            <li>Create a new input stream based on one of the demo streams</li>
            <li>Design a new multiview layout with a different arrangement</li>
            <li>Configure a new output stream that uses your custom layout</li>
        </ul>
        
        <h3 class="text-xl font-medium mb-3">Testing Stream Management</h3>
        <ul class="list-disc pl-6 space-y-2 mb-4">
            <li>Practice starting and stopping the demo output streams</li>
            <li>Monitor the stream health dashboard to see status indicators</li>
            <li>Try changing a stream's status and observe the system's response</li>
        </ul>
        
        <div class="bg-green-50 border-l-4 border-green-500 p-4 my-4">
            <p class="text-green-700">
                <strong>Tip:</strong> Since the demo stream URLs don't point to real sources, the system will show them as "Disconnected" or "Error" in the health monitoring. This is expected behavior and doesn't indicate a problem with the system itself.
            </p>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4" id="removing-demo-data">5. Removing Demo Data</h2>
        <p class="mb-4">
            If you want to remove the demo data and start with a clean database, you can use the following command:
        </p>
        
        <div class="bg-gray-800 text-white p-3 rounded font-mono text-sm mb-4">
            php artisan migrate:fresh
        </div>
        
        <div class="bg-red-50 border-l-4 border-red-500 p-4 my-4">
            <p class="text-red-700">
                <strong>Warning:</strong> This will remove ALL data from your database, including any custom configurations you've created. Only use this command if you want to start completely fresh.
            </p>
        </div>
        
        <p class="mb-4">
            If you want to keep your custom data but remove only the demo data, you'll need to manually delete the demo records through the admin interface.
        </p>
    </div>

    <div class="mt-12 pt-6 border-t border-gray-200">
        <h2 class="text-2xl font-semibold mb-4">Next Steps</h2>
        <p>
            Now that you understand the demo data, you can:
        </p>
        <ul class="list-disc pl-6 space-y-2 mt-4">
            <li>Read the <a href="{{ route('guides.show', 'stream-management-guide') }}" class="text-blue-600 hover:underline">Stream Management Guide</a> to learn how to manage streams</li>
            <li>Explore the <a href="{{ route('guides.show', 'multiview-layout-guide') }}" class="text-blue-600 hover:underline">Multiview Layout Configuration Guide</a> to create custom layouts</li>
            <li>Check the <a href="{{ route('guides.show', 'output-stream-guide') }}" class="text-blue-600 hover:underline">Output Stream Configuration Guide</a> for detailed output settings</li>
        </ul>
    </div>
</div>
@endsection 