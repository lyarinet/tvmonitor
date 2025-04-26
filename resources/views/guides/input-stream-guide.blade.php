@extends('layouts.guide')

@section('title', 'Input Stream Management')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Input Stream Management</h1>
    
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Overview</h2>
        <p class="mb-4">
            This guide explains how to configure and manage input video streams in the TV Monitor System. Input streams are the source video feeds that can be used in multiview layouts and output streams.
        </p>
        <p class="mb-4">
            The system supports various streaming protocols with advanced configuration options for professional broadcast environments, including MPEG-TS over UDP with program scanning capabilities.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Creating Input Streams</h2>
        <p class="mb-4">
            To create a new input stream, follow these steps:
        </p>
        <ol class="list-decimal list-inside mb-4 space-y-2">
            <li>Navigate to the <strong>Input Streams</strong> section in the main menu.</li>
            <li>Click the <strong>+ New Input Stream</strong> button in the top-right corner.</li>
            <li>Fill in the form with the required information:
                <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                    <li><strong>Name:</strong> A descriptive name for the stream (e.g., "Camera 1" or "Studio Feed").</li>
                    <li><strong>Source URL:</strong> The URL or path to the video source. This can be an RTMP, HLS, or other supported stream URL.</li>
                    <li><strong>Stream Type:</strong> Select the appropriate type (e.g., RTMP, HLS, UDP/MPEG-TS).</li>
                    <li><strong>Status:</strong> Enable or disable the stream.</li>
                    <li><strong>Program ID (PID):</strong> For MPEG-TS streams, specify the Program ID to extract.</li>
                    <li><strong>Auto-Scan:</strong> Enable automatic program scanning for multiprogram transport streams.</li>
                </ul>
            </li>
            <li>Click <strong>Create</strong> to save the new input stream.</li>
        </ol>
    </div>

    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-500 p-4">
        <h3 class="text-lg font-semibold text-yellow-800">Important Note</h3>
        <p>
            Input streams must be properly configured with valid URLs for the system to successfully capture and process the video feed. If you're having issues, check that your source is accessible from the server where the TV Monitor System is running.
        </p>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Managing Input Streams</h2>
        <p class="mb-4">
            After creating input streams, you can:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>Edit stream details:</strong> Click on the stream name in the list to modify its properties.</li>
            <li><strong>Toggle status:</strong> Use the toggle switch to quickly enable or disable a stream.</li>
            <li><strong>Preview stream:</strong> Click the eye icon to see a live preview of the input stream.</li>
            <li><strong>Delete stream:</strong> Click the trash icon to remove a stream from the system.</li>
            <li><strong>Scan for programs:</strong> For UDP/MPEG-TS streams, manually trigger program scanning.</li>
        </ul>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">UDP Stream Configuration</h2>
        <p class="mb-4">
            UDP streams, especially those carrying MPEG Transport Stream (MPEG-TS) data, require additional configuration:
        </p>
        
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Program Scanning</h3>
                <p class="text-sm mb-2">The system can automatically scan UDP multicast/unicast streams to detect available programs:</p>
                <ol class="list-decimal list-inside text-sm space-y-1">
                    <li>Create a new UDP input stream with the multicast/unicast address</li>
                    <li>Enable the "Auto-Scan" option during setup</li>
                    <li>The system will analyze the transport stream and detect all available programs</li>
                    <li>Select the desired program from the detected list or let the system monitor all programs</li>
                </ol>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Example Manual Scan Command:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded">ffprobe -i udp://239.0.0.1:1234 -show_programs</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Program ID (PID) Selection</h3>
                <p class="text-sm mb-2">For multiprogram transport streams, you need to specify which program to capture:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li><strong>Program Number:</strong> The numerical identifier for a specific program (typically 1-65535)</li>
                    <li><strong>Service Name:</strong> Human-readable name for the program (e.g., "BBC One", "News Channel")</li>
                    <li><strong>PMT PID:</strong> Program Map Table PID that identifies audio/video elementary streams</li>
                    <li><strong>PCR PID:</strong> Program Clock Reference PID for timing synchronization</li>
                </ul>
                <div class="mt-3 p-2 border border-gray-200 rounded-md">
                    <table class="min-w-full text-xs">
                        <tr class="bg-gray-50">
                            <th class="p-1 text-left">Stream Type</th>
                            <th class="p-1 text-left">Description</th>
                            <th class="p-1 text-left">PID Range</th>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">0x00</td>
                            <td class="p-1 border-t">Reserved</td>
                            <td class="p-1 border-t">-</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">0x01, 0x02</td>
                            <td class="p-1 border-t">MPEG-1, MPEG-2 Video</td>
                            <td class="p-1 border-t">0x0010-0x1FFE</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">0x03, 0x04</td>
                            <td class="p-1 border-t">MPEG-1, MPEG-2 Audio</td>
                            <td class="p-1 border-t">0x0010-0x1FFE</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">0x1B</td>
                            <td class="p-1 border-t">H.264 Video</td>
                            <td class="p-1 border-t">0x0010-0x1FFE</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">0x24</td>
                            <td class="p-1 border-t">H.265/HEVC Video</td>
                            <td class="p-1 border-t">0x0010-0x1FFE</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Network Configuration</h3>
                <p class="text-sm mb-2">For UDP multicast streams, network configuration is critical:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li><strong>IGMP Version:</strong> Set appropriate IGMP version (v2 or v3) for multicast group membership</li>
                    <li><strong>Interface Selection:</strong> Specify which network interface to use for receiving multicast</li>
                    <li><strong>TTL (Time-to-Live):</strong> Configure appropriate TTL value for multicast packets</li>
                    <li><strong>Buffer Size:</strong> Adjust UDP buffer size to prevent packet loss (e.g., 25MB)</li>
                </ul>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Example URL with Network Parameters:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded">udp://239.0.0.1:1234?fifo_size=1000000&buffer_size=25000000&overrun_nonfatal=1&localaddr=192.168.212.252</code>
                </div>
                
                <div class="mt-3 ml-4 bg-blue-50 border-l-4 border-blue-400 p-3 rounded text-sm">
                    <h4 class="font-semibold text-blue-800">Local Address Configuration</h4>
                    <p class="mb-2">The <code class="bg-blue-100 px-1 rounded">localaddr</code> parameter is especially important in multi-homed servers (servers with multiple network interfaces or IP addresses):</p>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        <li>Use <code class="bg-blue-100 px-1 rounded">localaddr=192.168.212.252</code> to specify which network interface should be used for receiving the UDP stream</li>
                        <li>This is essential when your server has multiple network interfaces and you need to ensure that multicast traffic comes through a specific interface</li>
                        <li>For IGMP-based multicast, this ensures that membership reports are sent from the correct interface</li>
                        <li>Without this parameter, the system may use the default interface which might not have access to the multicast group</li>
                    </ul>
                    <p class="mt-2">Example using the dedicated streaming network interface:</p>
                    <code class="font-mono text-xs bg-blue-100 p-1 rounded block mt-1">udp://239.255.1.1:5000?localaddr=192.168.212.252&buffer_size=25000000</code>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Monitoring Stream Health</h2>
        <p class="mb-4">
            The TV Monitor System continuously monitors the health of your input streams. The status indicators show:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-2"></span> <strong>Green:</strong> Stream is active and healthy.</li>
            <li><span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-2"></span> <strong>Yellow:</strong> Stream has intermittent issues.</li>
            <li><span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-2"></span> <strong>Red:</strong> Stream is down or inaccessible.</li>
            <li><span class="inline-block w-3 h-3 rounded-full bg-gray-500 mr-2"></span> <strong>Gray:</strong> Stream is disabled.</li>
        </ul>
        <div class="mt-4">
            <p class="mb-2 font-semibold">UDP-Specific Monitoring Metrics:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Packet Loss Rate:</strong> Percentage of UDP packets lost during transmission</li>
                <li><strong>Jitter:</strong> Variation in packet arrival time (ms)</li>
                <li><strong>CC Errors:</strong> Continuity Counter errors in the transport stream</li>
                <li><strong>PCR Accuracy:</strong> Accuracy of Program Clock Reference timing</li>
                <li><strong>PID Presence:</strong> Monitoring of expected PIDs in the transport stream</li>
            </ul>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Advanced MPEG-TS Analysis</h2>
        <p class="mb-4">The system provides detailed analysis tools for MPEG Transport Streams:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">PSI/SI Tables</h3>
                <p class="text-sm mb-2">Monitor and analyze Program Specific Information tables:</p>
                <ul class="list-disc list-inside text-xs">
                    <li><strong>PAT (Program Association Table):</strong> Links program numbers with PMT PIDs</li>
                    <li><strong>PMT (Program Map Table):</strong> Identifies PIDs for program elements</li>
                    <li><strong>SDT (Service Description Table):</strong> Program names and information</li>
                    <li><strong>EIT (Event Information Table):</strong> Electronic Program Guide data</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Stream Analysis</h3>
                <p class="text-sm mb-2">Detailed metrics for each elementary stream:</p>
                <ul class="list-disc list-inside text-xs">
                    <li><strong>Bitrate:</strong> Current and average bitrate per PID</li>
                    <li><strong>Frame Rate:</strong> Video frame rate monitoring</li>
                    <li><strong>Resolution:</strong> Video resolution detection</li>
                    <li><strong>Audio Levels:</strong> Audio level monitoring</li>
                    <li><strong>Codec Information:</strong> Detailed codec parameters</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Supported Stream Types</h2>
        <p class="mb-4">The system supports several types of input streams:</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 shadow-sm rounded-lg">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Type</th>
                        <th class="py-2 px-4 border-b text-left">Description</th>
                        <th class="py-2 px-4 border-b text-left">URL Format Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 px-4 border-b">RTMP</td>
                        <td class="py-2 px-4 border-b">Real-Time Messaging Protocol</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">rtmp://server/live/stream</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">HLS</td>
                        <td class="py-2 px-4 border-b">HTTP Live Streaming</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">https://server/path/playlist.m3u8</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">RTSP</td>
                        <td class="py-2 px-4 border-b">Real-Time Streaming Protocol</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">rtsp://username:password@camera-ip:554/stream</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP/MPEG-TS</td>
                        <td class="py-2 px-4 border-b">User Datagram Protocol with MPEG Transport Stream</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?pmt_pid=256&program=1</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">SRT</td>
                        <td class="py-2 px-4 border-b">Secure Reliable Transport</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">srt://server:9999?latency=200&pbkeylen=32</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">File</td>
                        <td class="py-2 px-4 border-b">Local video file (for testing)</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">/path/to/video.mp4</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Advanced Usage Scenarios</h2>
        <p class="mb-4">
            Here are some common advanced configurations for specific broadcast environments:
        </p>
        
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">DVB-T/T2 Reception</h3>
                <p class="text-sm mb-2">For receiving digital terrestrial television:</p>
                <ol class="list-decimal list-inside text-sm space-y-1">
                    <li>Connect a compatible DVB-T/T2 tuner to your server</li>
                    <li>Use the system's auto-discovery to detect the tuner hardware</li>
                    <li>Set frequency, bandwidth, and other tuning parameters</li>
                    <li>Enable program scanning to discover all broadcast channels</li>
                </ol>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Example Configuration:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Input Name: DVB-T Antenna 1</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Source: dvb://adapter=0&frequency=578000000&bandwidth=8&modulation=64QAM</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Auto-scan: Enabled</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Redundant Source Configuration</h3>
                <p class="text-sm mb-2">For mission-critical streams, implement automatic failover:</p>
                <ol class="list-decimal list-inside text-sm space-y-1">
                    <li>Create a primary input stream with your main source</li>
                    <li>Set up a backup input stream with an alternate source</li>
                    <li>Create a redundant stream that monitors both inputs</li>
                    <li>Configure failover parameters (detection time, switching threshold)</li>
                </ol>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Example Redundant Configuration:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Primary: udp://239.0.0.1:1234?localaddr=192.168.212.252</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Backup: rtmp://backup-server/live/channel1</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Failover delay: 3 seconds</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">Recovery mode: Automatic with 30s stability verification</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold mb-2">Transport Stream Remultiplexing</h3>
                <p class="text-sm mb-2">Extract and recombine programs from multiple transport streams:</p>
                <ol class="list-decimal list-inside text-sm space-y-1">
                    <li>Create input streams for each source transport stream</li>
                    <li>Select specific programs from each source using Program IDs</li>
                    <li>Create a new output stream that combines these programs</li>
                    <li>Optionally remap PIDs to avoid conflicts</li>
                </ol>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Example PID Mapping for Remultiplexing:</p>
                    <table class="min-w-full text-xs mt-1">
                        <tr class="bg-gray-100">
                            <th class="p-1 text-left">Source</th>
                            <th class="p-1 text-left">Original PID</th>
                            <th class="p-1 text-left">Remapped PID</th>
                            <th class="p-1 text-left">Description</th>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Source 1</td>
                            <td class="p-1 border-t">0x100</td>
                            <td class="p-1 border-t">0x100</td>
                            <td class="p-1 border-t">Video PID (unchanged)</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Source 1</td>
                            <td class="p-1 border-t">0x101</td>
                            <td class="p-1 border-t">0x101</td>
                            <td class="p-1 border-t">Audio PID (unchanged)</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Source 2</td>
                            <td class="p-1 border-t">0x100</td>
                            <td class="p-1 border-t">0x200</td>
                            <td class="p-1 border-t">Video PID (remapped)</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">Source 2</td>
                            <td class="p-1 border-t">0x101</td>
                            <td class="p-1 border-t">0x201</td>
                            <td class="p-1 border-t">Audio PID (remapped)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Practical Stream URL Examples</h2>
        <p class="mb-4">
            Below are practical examples of stream URLs for various scenarios:
        </p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 shadow-sm rounded-lg">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border-b text-left">Scenario</th>
                        <th class="py-2 px-4 border-b text-left">URL Example</th>
                        <th class="py-2 px-4 border-b text-left">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-2 px-4 border-b">Basic UDP Multicast</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234</td>
                        <td class="py-2 px-4 border-b">Simple multicast reception</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP with Interface Binding</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?localaddr=192.168.212.252</td>
                        <td class="py-2 px-4 border-b">Binds to specific network interface</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP with Program Selection</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?program=1</td>
                        <td class="py-2 px-4 border-b">Selects program #1 from transport stream</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP with PID Selection</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?pmt_pid=1000</td>
                        <td class="py-2 px-4 border-b">Specifies PMT PID</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP with Buffer Settings</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?buffer_size=25000000&fifo_size=1000000</td>
                        <td class="py-2 px-4 border-b">Large buffer for unstable networks</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">UDP with Error Tolerance</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?overrun_nonfatal=1&timeout=5000000</td>
                        <td class="py-2 px-4 border-b">Continues despite packet loss</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-4 border-b">Complete UDP Configuration</td>
                        <td class="py-2 px-4 border-b font-mono text-sm">udp://239.0.0.1:1234?localaddr=192.168.212.252&buffer_size=25000000&program=1&pmt_pid=256&timeout=5000000</td>
                        <td class="py-2 px-4 border-b">Combined parameters for robust setup</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Troubleshooting</h2>
        <p class="mb-4">If you encounter issues with input streams, try these steps:</p>
        
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Stream Not Available</h3>
                <p class="text-sm mb-2">If a stream shows as unavailable:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Verify the source URL is correct and accessible</li>
                    <li>Check network connectivity between the server and source</li>
                    <li>Ensure any required authentication credentials are correct</li>
                    <li>Restart the source stream at its origin</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">UDP/MPEG-TS Specific Issues</h3>
                <p class="text-sm mb-2">For UDP multicast stream problems:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Check multicast routing and IGMP settings on network switches</li>
                    <li>Verify correct PID values for video, audio, and PMT</li>
                    <li>Check for packet loss using network monitoring tools</li>
                    <li>Ensure the multicast group is correctly subscribed to by the server</li>
                    <li>For Program ID issues, use the scan tool to verify available programs</li>
                </ul>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Diagnostic Commands:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded">tcpdump -i eth0 udp port 1234</code><br>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded mt-1">ffprobe -i udp://239.0.0.1:1234 -show_streams -show_programs</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Poor Stream Quality</h3>
                <p class="text-sm mb-2">If a stream has quality issues:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Check your network bandwidth and connection stability</li>
                    <li>Reduce the source stream bitrate if network capacity is limited</li>
                    <li>Verify there are no network bottlenecks between source and server</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Stream Latency</h3>
                <p class="text-sm mb-2">To reduce stream latency:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Use low-latency protocols like RTMP instead of HLS where possible</li>
                    <li>Adjust buffer settings in the advanced configuration</li>
                    <li>Consider using a CDN for widespread distribution</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Advanced Network Diagnostics</h3>
                <p class="text-sm mb-2">For deeper network-related issues with UDP streams:</p>
                <ul class="list-disc list-inside text-sm">
                    <li>Use multicast route tracing tools to verify multicast path</li>
                    <li>Check if IGMP snooping is properly configured on network switches</li>
                    <li>Verify that no firewall is blocking UDP traffic</li>
                    <li>Ensure QoS settings prioritize streaming traffic</li>
                </ul>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">Network Diagnostic Commands:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Check multicast routing table</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">ip mroute show</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Check if packets are reaching interface</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">tcpdump -i eth0 host 239.0.0.1 -vv</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Verify IGMP membership</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">netstat -gn</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Transport Stream Analysis Tools</h3>
                <p class="text-sm mb-2">Advanced tools for diagnosing MPEG-TS issues:</p>
                <ul class="list-disc list-inside text-sm">
                    <li><strong>tsanalyze:</strong> Comprehensive TS analysis for standards compliance</li>
                    <li><strong>tsrdump:</strong> Extract and analyze specific PIDs from a transport stream</li>
                    <li><strong>TS Reader:</strong> GUI-based analysis tool for transport streams</li>
                    <li><strong>FFprobe:</strong> Part of FFmpeg, provides detailed stream information</li>
                </ul>
                <div class="mt-3 ml-4 bg-gray-50 p-3 rounded text-sm">
                    <p class="font-semibold">TS Analysis Commands:</p>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Check all programs in a stream</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">ffprobe -i udp://239.0.0.1:1234 -show_programs</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Detailed PID analysis</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">ffprobe -i udp://239.0.0.1:1234 -show_streams -select_streams p:0</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1"># Capture raw transport stream for analysis</code>
                    <code class="font-mono text-xs bg-gray-100 p-1 rounded block mt-1">ffmpeg -i udp://239.0.0.1:1234 -c copy -t 60 capture.ts</code>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border border-gray-300">
                <h3 class="font-semibold">Common Error Codes and Solutions</h3>
                <p class="text-sm mb-2">Troubleshooting specific error codes:</p>
                <div class="overflow-x-auto mt-2">
                    <table class="min-w-full text-xs">
                        <tr class="bg-gray-50">
                            <th class="p-1 text-left">Error Code</th>
                            <th class="p-1 text-left">Description</th>
                            <th class="p-1 text-left">Possible Solutions</th>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">TS-E001</td>
                            <td class="p-1 border-t">No PAT detected</td>
                            <td class="p-1 border-t">Check source stream integrity, verify multicast reception</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">TS-E002</td>
                            <td class="p-1 border-t">PMT not found for program</td>
                            <td class="p-1 border-t">Verify program number, use scan tool to confirm program list</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">TS-E003</td>
                            <td class="p-1 border-t">PID not found in stream</td>
                            <td class="p-1 border-t">Confirm PID values, check if PIDs have changed at source</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">TS-E004</td>
                            <td class="p-1 border-t">High continuity counter errors</td>
                            <td class="p-1 border-t">Network issues causing packet loss, increase buffer size</td>
                        </tr>
                        <tr>
                            <td class="p-1 border-t">TS-E005</td>
                            <td class="p-1 border-t">PCR discontinuity</td>
                            <td class="p-1 border-t">Source stream timing issues, adjust PCR tolerance settings</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Using Input Streams</h2>
        <p class="mb-4">
            Once you've set up your input streams, you can use them in:
        </p>
        <ul class="list-disc list-inside space-y-2">
            <li><strong>Multiview Layouts:</strong> Create custom layouts with multiple input streams for monitoring purposes.</li>
            <li><strong>Output Streams:</strong> Repackage input streams into new output streams with different formats or quality settings.</li>
            <li><strong>Recording:</strong> Record input streams for later playback or archiving.</li>
            <li><strong>Program Extraction:</strong> Extract specific programs from multiprogram transport streams.</li>
        </ul>
        <p class="mt-4">
            For more information on using input streams in multiview layouts, see the <a href="{{ route('guides.show', 'multiview-layout-guide') }}" class="text-blue-600 hover:underline">Multiview Layout Configuration Guide</a>.
        </p>
    </div>
</div>
@endsection 