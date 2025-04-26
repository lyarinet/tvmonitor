<?php

namespace App\Http\Controllers;

use App\Models\OutputStream;
use App\Models\MultiviewLayout;
use App\Models\LayoutPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StreamViewController extends Controller
{
    /**
     * Display a multiview layout with active streams.
     *
     * @param int $id The multiview layout ID or output stream ID
     * @return \Illuminate\View\View
     */
    public function viewMultiview($id)
    {
        // First check if this is a multiview layout ID
        $layout = MultiviewLayout::with(['layoutPositions.inputStream'])->find($id);
        
        // If no layout found, check if it's an output stream ID
        if (!$layout) {
            \Illuminate\Support\Facades\Log::info("No multiview layout found with ID: {$id}, checking if it's an output stream ID");
            
            $outputStream = OutputStream::find($id);
            
            if (!$outputStream) {
                \Illuminate\Support\Facades\Log::warning("No multiview layout or output stream found with ID: {$id}");
                return abort(404, 'Multiview not found');
            }
            
            \Illuminate\Support\Facades\Log::info("Found output stream with ID: {$id}", [
                'name' => $outputStream->name,
                'status' => $outputStream->status,
                'protocol' => $outputStream->protocol,
                'multiview_layout_id' => $outputStream->multiview_layout_id
            ]);
            
            // If this is an output stream, get its layout if it has one
            if ($outputStream->multiview_layout_id) {
                $layout = MultiviewLayout::with(['layoutPositions.inputStream'])->find($outputStream->multiview_layout_id);
                
                if ($layout) {
                    \Illuminate\Support\Facades\Log::info("Using multiview layout {$layout->id} for output stream {$id}");
                } else {
                    \Illuminate\Support\Facades\Log::warning("Output stream {$id} references non-existent multiview layout ID: {$outputStream->multiview_layout_id}");
                }
            }
            
            if (!$layout) {
                // If we don't have a layout, use a single-stream view
                \Illuminate\Support\Facades\Log::info("No valid layout found for output stream {$id}, falling back to single stream view");
                return $this->viewSingleStream($outputStream);
            }
        } else {
            \Illuminate\Support\Facades\Log::info("Found multiview layout with ID: {$id}", [
                'name' => $layout->name,
                'positions_count' => $layout->layoutPositions->count()
            ]);
        }
        
        // Prepare the positions with stream info
        $streamData = [];
        $layoutWidth = $layout->width ?: 1920;
        $layoutHeight = $layout->height ?: 1080;
        $isPreview = true;

        // Check if any streams are active
        $activeStreamCount = 0;

        foreach ($layout->layoutPositions as $position) {
            // Check if the position has a valid input stream
            $inputStream = $position->inputStream;
            $hasStream = $inputStream !== null;
            $isActive = $hasStream && $inputStream->status === 'active';
            
            if ($isActive) {
                $activeStreamCount++;
            }
            
            // Get proxy URL for the stream if it exists
            $streamProxyUrl = '';
            $streamProtocol = '';
            if ($hasStream) {
                // Verify input stream details
                \Illuminate\Support\Facades\Log::debug("Position stream details", [
                    'position_id' => $position->id,
                    'input_stream_id' => $inputStream->id,
                    'input_stream_name' => $inputStream->name,
                    'status' => $inputStream->status,
                    'protocol' => $inputStream->protocol ?? 'unknown'
                ]);
                
                $streamProxyUrl = url("/stream-proxy/{$inputStream->id}/playlist.m3u8");
                $streamProtocol = $inputStream->protocol ?? 'unknown';
                
                // For UDP streams, we need a special approach since they can't be played directly
                if ($streamProtocol === 'udp') {
                    // Check if there's an associated output stream with HLS 
                    $outputStream = \App\Models\OutputStream::where('status', 'active')
                        ->where(function($query) use ($inputStream) {
                            $query->where('metadata->input_stream_id', $inputStream->id)
                                ->orWhere('metadata', 'like', '%"input_stream_id":' . $inputStream->id . '%');
                        })
                        ->where('protocol', 'hls')
                        ->first();
                    
                    if ($outputStream) {
                        \Illuminate\Support\Facades\Log::info("Found HLS output stream for UDP input {$inputStream->id}: Output ID {$outputStream->id}");
                        $streamProxyUrl = url("/stream-proxy/{$outputStream->id}/playlist.m3u8");
                        $streamProtocol = 'hls'; // Converted to HLS by the output stream
                    } else {
                        \Illuminate\Support\Facades\Log::warning("UDP stream {$inputStream->id} has no associated HLS output stream, it cannot be played in browser");
                        // UDP can't be played directly in a browser, so mark it as not streaming
                        $isActive = false;
                    }
                }
                
                // Verify the stream files exist for HLS streams
                if ($streamProtocol === 'hls' && $isActive) {
                    $streamId = $outputStream->id ?? $inputStream->id;
                    $basePath = storage_path("app/public/streams/{$streamId}");
                    $playlistPath = "{$basePath}/playlist.m3u8";
                    
                    // Check if playlist file exists
                    $playlistExists = file_exists($playlistPath);
                    
                    // Find segments
                    $segments = glob("{$basePath}/segment_*.ts");
                    $segmentCount = count($segments);
                    $hasSegments = $segmentCount > 0;
                    
                    \Illuminate\Support\Facades\Log::debug("HLS stream status check for ID {$streamId}", [
                        'playlist_exists' => $playlistExists,
                        'segments_count' => $segmentCount,
                        'base_path' => $basePath
                    ]);
                    
                    // Mark as inactive if files don't exist
                    if (!$playlistExists && !$hasSegments) {
                        \Illuminate\Support\Facades\Log::warning("HLS stream {$streamId} has no playlist or segments, marking as inactive");
                        $isActive = false;
                    }
                }
            }
            
            $streamData[] = [
                'position_x' => $position->position_x,
                'position_y' => $position->position_y,
                'width' => $position->width,
                'height' => $position->height,
                'input_name' => $inputStream->name ?? null,
                'show_label' => $position->show_label ?? true,
                'label_position' => $position->label_position ?? 'bottom',
                'has_stream' => $hasStream && $isActive,
                'is_active' => $isActive,
                'stream_id' => $inputStream->id ?? null,
                'stream_url' => $streamProxyUrl,
                'protocol' => $streamProtocol,
            ];
        }

        // If more than 0 streams are active, consider this a live layout
        $isPreview = $activeStreamCount === 0;

        // Log layout info for debugging
        \Illuminate\Support\Facades\Log::info("Multiview layout render details", [
            'layout_id' => $layout->id,
            'active_stream_count' => $activeStreamCount,
            'total_positions' => count($streamData),
            'is_preview' => $isPreview
        ]);
        
        return view('stream.grid-layout', [
            'layout' => $layout,
            'streamData' => $streamData,
            'isPreview' => $isPreview,
            'layoutWidth' => $layoutWidth,
            'layoutHeight' => $layoutHeight
        ]);
    }
    
    /**
     * View a single stream (used when no layout is available)
     * 
     * @param OutputStream $outputStream
     * @return \Illuminate\View\View
     */
    private function viewSingleStream($outputStream)
    {
        // Check if the stream is active
        $isActive = $outputStream->status === 'active';
        
        // For HLS streams, we need to check if the playlist exists and if there are segments
        $isLiveStream = false;
        $playlistUrl = '';
        $hasSegments = false;
        $segmentCount = 0;
        
        if ($isActive) {
            $id = $outputStream->id;
            $basePath = storage_path("app/public/streams/{$id}");
            $playlistPath = "{$basePath}/playlist.m3u8";
            
            // Check if playlist file exists
            $playlistExists = file_exists($playlistPath);
            
            // Find segments
            $segments = glob("{$basePath}/segment_*.ts");
            $segmentCount = count($segments);
            $hasSegments = $segmentCount > 0;
            
            // Log details for debugging
            \Illuminate\Support\Facades\Log::info("Stream status check for single view {$id}", [
                'playlist_exists' => $playlistExists,
                'segments_count' => $segmentCount,
                'is_active' => $isActive,
                'protocol' => $outputStream->protocol ?? 'unknown'
            ]);
            
            // Consider the stream live if active and either playlist exists or segments are found
            if ($playlistExists || $hasSegments) {
                $isLiveStream = true;
                // Use the proxy URL for the playlist
                $playlistUrl = url("/stream-proxy/{$id}/playlist.m3u8");
            }
        }
        
        // Get stream status info
        $stream = [
            'id' => $outputStream->id,
            'name' => $outputStream->name,
            'status' => $outputStream->status,
            'has_stream' => $isLiveStream && ($hasSegments || !empty($playlistUrl)),
            'input_name' => $outputStream->name ?? 'Stream'
        ];
        
        return view('stream.multiview', [
            'stream' => $stream,
            'isLiveStream' => $isLiveStream,
            'playlistUrl' => $playlistUrl,
            'multiviewId' => $outputStream->id,
            'hasSegments' => $hasSegments,
            'segmentCount' => $segmentCount
        ]);
    }
    
    /**
     * List all viewable multiview streams.
     *
     * @return \Illuminate\View\View
     */
    public function listViewableStreams()
    {
        // Get all active output streams with HLS protocol (most compatible for web viewing)
        $activeStreams = OutputStream::where('status', 'active')
            ->where('protocol', 'hls')
            ->orderBy('name')
            ->with('multiviewLayout')
            ->get();
            
        // Get all multiview layouts
        $layouts = MultiviewLayout::orderBy('name')->get();
        
        return view('stream.list', [
            'activeStreams' => $activeStreams,
            'layouts' => $layouts,
        ]);
    }

    /**
     * API endpoint to check the status of a multiview layout and its streams.
     * This can be called via AJAX to diagnose playback issues.
     *
     * @param int $id The multiview layout ID or output stream ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMultiviewStatus($id)
    {
        // First check if this is a multiview layout ID
        $layout = MultiviewLayout::with(['layoutPositions.inputStream'])->find($id);
        $outputStream = null;
        
        // If no layout found, check if it's an output stream ID
        if (!$layout) {
            $outputStream = OutputStream::find($id);
            
            if (!$outputStream) {
                return response()->json([
                    'error' => 'Multiview not found',
                    'id' => $id,
                    'type' => 'unknown'
                ], 404);
            }
            
            // If this is an output stream, get its layout if it has one
            if ($outputStream->multiview_layout_id) {
                $layout = MultiviewLayout::with(['layoutPositions.inputStream'])->find($outputStream->multiview_layout_id);
            }
            
            if (!$layout) {
                // If we don't have a layout, return output stream status
                return $this->checkSingleStreamStatus($outputStream);
            }
        }
        
        // Prepare the positions with stream info
        $streamStatuses = [];
        $activeStreamCount = 0;

        foreach ($layout->layoutPositions as $position) {
            // Check if the position has a valid input stream
            $inputStream = $position->inputStream;
            $hasStream = $inputStream !== null;
            $isActive = $hasStream && $inputStream->status === 'active';
            
            if ($isActive) {
                $activeStreamCount++;
            }
            
            // Get stream details
            $streamId = $inputStream->id ?? null;
            $filesStatus = null;
            $segmentCount = 0;
            
            if ($hasStream && $isActive) {
                $streamProxyUrl = url("/stream-proxy/{$streamId}/playlist.m3u8");
                $streamProtocol = $inputStream->protocol ?? 'unknown';
                
                // For UDP streams, we need to check the associated output stream
                if ($streamProtocol === 'udp') {
                    // Find an associated HLS output stream
                    $associatedStream = \App\Models\OutputStream::where('status', 'active')
                        ->where(function($query) use ($streamId) {
                            $query->where('metadata->input_stream_id', $streamId)
                                ->orWhere('metadata', 'like', '%"input_stream_id":' . $streamId . '%');
                        })
                        ->where('protocol', 'hls')
                        ->first();
                    
                    if ($associatedStream) {
                        $streamId = $associatedStream->id;
                        $streamProtocol = 'hls';
                    }
                }
                
                // Check if stream files exist
                if ($streamProtocol === 'hls') {
                    $basePath = storage_path("app/public/streams/{$streamId}");
                    $playlistPath = "{$basePath}/playlist.m3u8";
                    
                    // Check if directory and playlist exist
                    $directoryExists = file_exists($basePath);
                    $playlistExists = file_exists($playlistPath);
                    
                    // Check for segments
                    $segments = $directoryExists ? glob("{$basePath}/segment_*.ts") : [];
                    $segmentCount = count($segments);
                    
                    $filesStatus = [
                        'directory_exists' => $directoryExists,
                        'playlist_exists' => $playlistExists,
                        'segment_count' => $segmentCount,
                        'has_enough_segments' => $segmentCount >= 2
                    ];
                }
            }
            
            $streamStatuses[] = [
                'position_id' => $position->id,
                'position_x' => $position->position_x,
                'position_y' => $position->position_y,
                'width' => $position->width,
                'height' => $position->height,
                'has_stream' => $hasStream,
                'is_active' => $isActive,
                'input_stream_id' => $streamId,
                'input_name' => $inputStream->name ?? null,
                'protocol' => $inputStream->protocol ?? null,
                'url' => $inputStream->url ?? null,
                'files_status' => $filesStatus,
                'segment_count' => $segmentCount
            ];
        }
        
        // Check output stream status if applicable
        $outputStatus = null;
        if ($outputStream) {
            $basePath = storage_path("app/public/streams/{$outputStream->id}");
            $playlistPath = "{$basePath}/playlist.m3u8";
            
            // Check if directory and playlist exist
            $directoryExists = file_exists($basePath);
            $playlistExists = file_exists($playlistPath);
            
            // Check for segments
            $segments = $directoryExists ? glob("{$basePath}/segment_*.ts") : [];
            $segmentCount = count($segments);
            
            $outputStatus = [
                'id' => $outputStream->id,
                'name' => $outputStream->name,
                'status' => $outputStream->status,
                'protocol' => $outputStream->protocol,
                'url' => $outputStream->url,
                'directory_exists' => $directoryExists,
                'playlist_exists' => $playlistExists,
                'segment_count' => $segmentCount
            ];
        }
        
        return response()->json([
            'id' => $id,
            'type' => $outputStream ? 'output_stream' : 'multiview_layout',
            'layout' => [
                'id' => $layout->id,
                'name' => $layout->name,
                'width' => $layout->width,
                'height' => $layout->height,
                'position_count' => $layout->layoutPositions->count(),
                'active_stream_count' => $activeStreamCount
            ],
            'output_stream' => $outputStatus,
            'stream_statuses' => $streamStatuses
        ]);
    }
    
    /**
     * Check status of a single output stream
     *
     * @param OutputStream $outputStream
     * @return \Illuminate\Http\JsonResponse
     */
    private function checkSingleStreamStatus($outputStream)
    {
        $id = $outputStream->id;
        $basePath = storage_path("app/public/streams/{$id}");
        $playlistPath = "{$basePath}/playlist.m3u8";
        
        // Check if directory and playlist exist
        $directoryExists = file_exists($basePath);
        $playlistExists = file_exists($playlistPath);
        
        // Check for segments
        $segments = $directoryExists ? glob("{$basePath}/segment_*.ts") : [];
        $segmentCount = count($segments);
        
        return response()->json([
            'id' => $id,
            'type' => 'output_stream',
            'name' => $outputStream->name,
            'status' => $outputStream->status,
            'protocol' => $outputStream->protocol,
            'url' => $outputStream->url,
            'directory_exists' => $directoryExists,
            'playlist_exists' => $playlistExists,
            'segment_count' => $segmentCount,
            'segments' => array_map(function($segment) {
                return basename($segment);
            }, array_slice($segments, 0, 10)) // Return up to 10 segment names
        ]);
    }

    /**
     * Display the dashboard for a multiview layout.
     *
     * @param int $id The multiview layout ID
     * @return \Illuminate\View\View
     */
    public function dashboard($id)
    {
        // Check if user has access to the dashboard
        if (!auth()->check() || !auth()->user()->can('view_dashboard')) {
            abort(403, 'Unauthorized access to dashboard');
        }

        // Get the layout with its positions and input streams
        $layout = MultiviewLayout::with(['layoutPositions.inputStream'])->findOrFail($id);
        
        // Prepare stream statistics
        $stats = [
            'total_positions' => $layout->layoutPositions->count(),
            'active_streams' => 0,
            'offline_streams' => 0,
            'empty_slots' => 0
        ];
        
        // Process stream data
        $streamData = [];
        foreach ($layout->layoutPositions as $position) {
            $inputStream = $position->inputStream;
            $hasStream = $inputStream !== null;
            $isActive = $hasStream && $inputStream->status === 'active';
            
            // Update statistics
            if (!$hasStream) {
                $stats['empty_slots']++;
            } elseif ($isActive) {
                $stats['active_streams']++;
            } else {
                $stats['offline_streams']++;
            }
            
            $streamData[] = [
                'position_x' => $position->position_x,
                'position_y' => $position->position_y,
                'width' => $position->width,
                'height' => $position->height,
                'input_name' => $inputStream->name ?? null,
                'has_stream' => $hasStream,
                'is_active' => $isActive,
                'stream_id' => $inputStream->id ?? null,
            ];
        }
        
        return view('stream.grid-dashboard', [
            'layout' => $layout,
            'streamData' => $streamData,
            'stats' => $stats
        ]);
    }
} 