<div>
    <div class="p-4 bg-gray-800 rounded-lg shadow-md text-white">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-white">Active Output Streams</h2>
            <button wire:click="refresh" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
        
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-sm text-green-400 bg-gray-900 rounded-lg border border-green-500">
                {{ session('message') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-400 bg-gray-900 rounded-lg border border-red-500">
                {{ session('error') }}
            </div>
        @endif
        
        <div wire:poll.{{ $refreshInterval }}ms>
            @if (count($this->streams) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Protocol
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Details
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach ($this->streams as $stream)
                                <tr class="hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">
                                            {{ $stream['name'] }}
                                        </div>
                                        <div class="text-sm text-gray-300 truncate max-w-xs">
                                            {{ $stream['url'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if ($stream['protocol'] === 'hls') bg-green-900 text-green-300
                                            @elseif ($stream['protocol'] === 'udp') bg-blue-900 text-blue-300
                                            @elseif ($stream['protocol'] === 'rtsp') bg-purple-900 text-purple-300
                                            @else bg-gray-700 text-gray-300 @endif">
                                            {{ strtoupper($stream['protocol']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $stream['status_bg_class'] }} {{ $stream['status_text_class'] }}">
                                            {{ $stream['status_display'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        <div class="text-xs">
                                            <div><span class="font-semibold">PID:</span> {{ $stream['process']['id'] ?? 'N/A' }}</div>
                                            @if ($stream['protocol'] === 'hls' && !empty($stream['output_stats']))
                                                <div><span class="font-semibold">Segments:</span> {{ $stream['output_stats']['segment_count'] ?? 0 }}</div>
                                                <div><span class="font-semibold">Latest:</span> {{ $stream['output_stats']['latest_segment'] ?? 'None' }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        @if ($stream['status']['database'] == 'active')
                                            <button wire:click="stopStream('{{ $stream['id'] }}')" 
                                                    wire:confirm="Are you sure you want to stop {{ $stream['name'] }}?"
                                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                                Stop
                                            </button>
                                        @else
                                            <span class="text-gray-500">Stopped</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-4 text-center text-gray-400 bg-gray-800 rounded-lg border border-gray-700">
                    <p>No streams found.</p>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:init', () => {
            // Auto-refresh data every 10 seconds
            setInterval(() => {
                @this.refresh();
            }, {{ $refreshInterval }});
        });
    </script>
</div> 