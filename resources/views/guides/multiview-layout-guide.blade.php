@extends('layouts.guide')

@section('title', 'Multiview Layout Configuration')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Multiview Layout Configuration</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            This guide explains how to create and manage multiview layouts in the TV Monitor System. Multiview layouts allow you to arrange multiple input streams on a single screen for monitoring purposes.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Creating a New Layout</h2>
        <p class="mb-4">
            To create a new multiview layout, follow these steps:
        </p>
        <ol class="list-decimal list-inside mb-4 space-y-2">
            <li>Navigate to the <strong>Multiview Layouts</strong> section in the main menu.</li>
            <li>Click the <strong>+ New Layout</strong> button in the top-right corner.</li>
            <li>Fill in the basic information:
                <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                    <li><strong>Name:</strong> A descriptive name for the layout (e.g., "Studio Overview" or "4-Camera Grid").</li>
                    <li><strong>Resolution:</strong> The overall resolution of the layout (e.g., 1920x1080).</li>
                    <li><strong>Background Color:</strong> Choose a background color for empty areas.</li>
                </ul>
            </li>
            <li>Click <strong>Continue</strong> to proceed to the layout editor.</li>
        </ol>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Using the Layout Editor</h2>
        <p class="mb-4">
            The layout editor allows you to visually design your multiview layout:
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="font-semibold mb-2">Adding Stream Windows</h3>
                <p class="text-sm">
                    Click the <strong>Add Stream</strong> button to add a new window to your layout. You can then:
                </p>
                <ul class="list-disc list-inside text-sm mt-2">
                    <li>Drag to position the window</li>
                    <li>Resize using the corner handles</li>
                    <li>Select an input stream from the dropdown</li>
                </ul>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="font-semibold mb-2">Window Properties</h3>
                <p class="text-sm">
                    Select any window to access its properties panel:
                </p>
                <ul class="list-disc list-inside text-sm mt-2">
                    <li>Assign an input stream</li>
                    <li>Add labels and configure their position</li>
                    <li>Set precise position and size coordinates</li>
                    <li>Configure borders and visual indicators</li>
                </ul>
            </div>
        </div>
        
        <p class="mb-4">
            Tips for effective layout design:
        </p>
        <ul class="list-disc list-inside space-y-1">
            <li>Use the grid snap feature (toggle in toolbar) for precise alignment</li>
            <li>Maintain consistent spacing between windows for a professional look</li>
            <li>Consider using different sizes for primary and secondary feeds</li>
            <li>Add labels to all streams for easy identification</li>
        </ul>
    </div>

    <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4">
        <h3 class="text-lg font-semibold text-blue-800">Layout Templates</h3>
        <p class="mb-2">
            The system includes several pre-designed layout templates to get you started quickly:
        </p>
        <ul class="list-disc list-inside">
            <li><strong>2×2 Grid:</strong> Four equal-sized windows arranged in a grid</li>
            <li><strong>3×3 Grid:</strong> Nine equal-sized windows arranged in a grid</li>
            <li><strong>PiP Layout:</strong> One large window with a smaller picture-in-picture window</li>
            <li><strong>T-Bar:</strong> One large window on top with a row of smaller windows below</li>
        </ul>
        <p class="mt-2">
            To use a template, select it from the <strong>Templates</strong> dropdown when creating a new layout.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Managing Layouts</h2>
        <p class="mb-4">
            After creating layouts, you can:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>Edit layout:</strong> Click on the layout name to modify its design and properties.</li>
            <li><strong>Duplicate layout:</strong> Use the duplicate button to create a copy that you can modify.</li>
            <li><strong>Preview layout:</strong> Click the eye icon to see a live preview of the multiview layout.</li>
            <li><strong>Delete layout:</strong> Click the trash icon to remove a layout from the system.</li>
        </ul>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Advanced Features</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-semibold mb-2">Dynamic Text Overlays</h3>
                <p class="mb-2">
                    You can add dynamic text overlays to any window in your layout:
                </p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Select a window in the layout editor</li>
                    <li>Click <strong>Add Overlay</strong> and select <strong>Text</strong></li>
                    <li>Enter your text content, or use variables:
                        <ul class="list-disc list-inside ml-6 mt-1">
                            <li><code>{stream_name}</code> - Displays the input stream name</li>
                            <li><code>{current_time}</code> - Displays the current time</li>
                            <li><code>{status}</code> - Displays the stream status</li>
                        </ul>
                    </li>
                    <li>Configure font, size, color, and position</li>
                </ol>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold mb-2">Tally Indicators</h3>
                <p class="mb-2">
                    Add visual tally indicators to show stream status:
                </p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Select a window in the layout editor</li>
                    <li>Check the <strong>Show Tally</strong> option</li>
                    <li>Configure position (top, bottom, left, right)</li>
                    <li>Choose colors for different states</li>
                </ol>
                <p class="text-sm mt-2">
                    Tally indicators automatically update based on the stream status, showing green for active streams and red for offline streams.
                </p>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold mb-2">Audio Monitoring</h3>
                <p class="mb-2">
                    Configure audio monitoring for your multiview:
                </p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>In the layout settings, go to the <strong>Audio</strong> tab</li>
                    <li>Choose an audio monitoring mode:
                        <ul class="list-disc list-inside ml-6 mt-1">
                            <li><strong>Follow Selection:</strong> Audio follows the currently selected window</li>
                            <li><strong>Mix All:</strong> Mix audio from all streams</li>
                            <li><strong>Single Source:</strong> Select a specific stream for audio</li>
                        </ul>
                    </li>
                    <li>Configure volume levels and meters display options</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Layout Performance Considerations</h2>
        <p class="mb-4">
            When designing layouts, consider these performance recommendations:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>Stream Count:</strong> Each active stream requires system resources. For optimal performance on standard hardware, limit layouts to 9-12 streams.</li>
            <li><strong>Resolution:</strong> Higher resolutions require more processing power. Consider using lower resolution streams for smaller windows.</li>
            <li><strong>Visual Effects:</strong> Features like motion detection and audio visualization increase CPU usage. Disable these for performance-critical applications.</li>
            <li><strong>Hardware Acceleration:</strong> Enable hardware acceleration in system settings for better performance with multiple streams.</li>
        </ul>
    </div>

<div class="max-w-4xl mx-auto py-8 px-4">

    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Multiview Layout Configurations</h2>
        <p class="mb-4">
            The TV Monitor System supports various multiview layout configurations for different production workflows. Here are some common layout patterns:
        </p>
        
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Production Monitoring Layout</h3>
                <p class="text-sm mb-2">A standard production-oriented layout includes:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li><strong>Program Window:</strong> Large primary display (typically top) showing the currently active program</li>
                    <li><strong>Preview Window:</strong> Secondary display showing the next source to air</li>
                    <li><strong>Source Monitors:</strong> Smaller windows displaying various input streams</li>
                    <li><strong>Tally Indicators:</strong> Colored borders (red for program, green for preview) for visual identification</li>
                </ul>
                
                <div class="mt-4 border border-gray-300 p-3 rounded">
                    <p class="font-semibold text-center mb-2">Example Layout - Program & Preview</p>
                    <div class="bg-black p-2 text-white text-xs">
                        <div class="border-2 border-red-600 h-32 mb-2 flex items-center justify-center">
                            <p class="text-center">PROGRAM - News Studio Camera 1</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div class="border-2 border-green-500 h-20 flex items-center justify-center">
                                <p class="text-center">PREVIEW - News Studio Camera 2</p>
                            </div>
                            <div class="border border-gray-700 h-20 flex items-center justify-center">
                                <p class="text-center">Weather Graphics</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <div class="border border-gray-700 h-16 flex items-center justify-center">
                                <p class="text-center text-xxs">Sports Feed</p>
                            </div>
                            <div class="border border-gray-700 h-16 flex items-center justify-center">
                                <p class="text-center text-xxs">Int'l News</p>
                            </div>
                            <div class="border border-gray-700 h-16 flex items-center justify-center">
                                <p class="text-center text-xxs">Studio Clock</p>
                            </div>
                            <div class="border border-gray-700 h-16 flex items-center justify-center">
                                <p class="text-center text-xxs">Test Pattern</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-center mt-2">A production-oriented layout with a large program window, preview window, and smaller source monitors. Program and preview windows have colored borders for easy identification.</p>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Resolution and Aspect Ratio Configuration</h3>
                <p class="text-sm mb-2">Configure display parameters for each layout window:</p>
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full text-xs">
                        <tr class="bg-gray-50">
                            <th class="p-1 text-left">Display Element</th>
                            <th class="p-1 text-left">Recommended Size</th>
                            <th class="p-1 text-left">Aspect Ratio</th>
                            <th class="p-1 text-left">Additional Settings</th>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Program Window</td>
                            <td class="p-1 border-t">1280×720 or larger</td>
                            <td class="p-1 border-t">16:9</td>
                            <td class="p-1 border-t">Audio meters, timecode display</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Preview Window</td>
                            <td class="p-1 border-t">640×360 or larger</td>
                            <td class="p-1 border-t">16:9</td>
                            <td class="p-1 border-t">Safe area markers</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Source Monitors</td>
                            <td class="p-1 border-t">320×180 minimum</td>
                            <td class="p-1 border-t">16:9</td>
                            <td class="p-1 border-t">Source labels, status indicators</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Full Multiview Output</td>
                            <td class="p-1 border-t">1920×1080 or 3840×2160</td>
                            <td class="p-1 border-t">16:9</td>
                            <td class="p-1 border-t">Clock, UMD labels, audio meters</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Custom Layout Creation</h3>
                <p class="text-sm mb-2">Design your own multiview layouts with these steps:</p>
                <ol class="list-decimal list-inside text-sm space-y-1">
                    <li>Navigate to the <strong>Layouts</strong> section in the main menu</li>
                    <li>Click <strong>Create New Layout</strong> to start with a blank canvas</li>
                    <li>Set the overall output resolution (e.g., 1920×1080)</li>
                    <li>Add display windows by dragging and resizing on the canvas</li>
                    <li>Assign input streams to each window from your configured sources</li>
                    <li>Configure borders, labels, and indicators as needed</li>
                    <li>Save your layout with a descriptive name</li>
                </ol>
                <p class="text-sm mt-2">You can create multiple layouts for different production needs and switch between them instantly.</p>
            </div>
        </div>
    </div>
</div>


    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Using Multiview Layouts</h2>
        <p class="mb-4">
            After setting up your layouts, you can:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>View in Browser:</strong> Open the layout in any modern web browser for monitoring.</li>
            <li><strong>Full Screen Mode:</strong> Use the browser's full-screen mode (F11) for dedicated displays.</li>
            <li><strong>Output as Stream:</strong> Configure a layout as the source for an output stream to distribute the multiview to other systems.</li>
            <li><strong>Embed in Dashboard:</strong> Embed layouts in custom dashboards using the provided embed code.</li>
        </ul>
        <p class="mt-4">
            For information on how to configure input streams for your layouts, see the <a href="{{ route('guides.show', 'input-stream-guide') }}" class="text-blue-600 hover:underline">Input Stream Management Guide</a>.
        </p>
    </div>
</div>
@endsection 