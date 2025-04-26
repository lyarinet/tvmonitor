# Code Snippets

## 1. Laravel Backend: Ingest an HLS Stream

### StreamIngestService.php
```php
<?php

namespace App\Services\Ingest;

use App\Models\Channel;
use App\Models\StreamInput;
use App\Models\StreamStatus;
use App\Events\StreamStatusChanged;
use App\Exceptions\InvalidStreamException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StreamIngestService
{
    /**
     * Ingest an HLS stream and prepare it for monitoring
     *
     * @param Channel $channel
     * @param string $streamUrl
     * @return bool
     * @throws InvalidStreamException
     */
    public function ingestHlsStream(Channel $channel, string $streamUrl): bool
    {
        // Validate the HLS stream
        if (!$this->validateHlsStream($streamUrl)) {
            throw new InvalidStreamException("Invalid HLS stream: {$streamUrl}");
        }

        // Create or update stream input
        $streamInput = StreamInput::updateOrCreate(
            ['channel_id' => $channel->id, 'type' => 'hls'],
            [
                'url' => $streamUrl,
                'active' => true,
                'settings' => json_encode([
                    'buffer_size' => 5000,
                    'reconnect_delay' => 3,
                    'timeout' => 10
                ])
            ]
        );

        // Update stream status
        $status = StreamStatus::updateOrCreate(
            ['channel_id' => $channel->id],
            [
                'status' => 'online',
                'is_online' => true,
                'last_online' => now(),
                'bitrate' => 0, // Will be updated by monitoring service
                'details' => json_encode([
                    'resolution' => null,
                    'codec' => null,
                    'frame_rate' => null
                ])
            ]
        );

        // Dispatch job to process stream (generate thumbnails, etc.)
        dispatch(new \App\Jobs\ProcessStream($channel));

        // Broadcast status change
        event(new StreamStatusChanged($channel, $status));

        Log::info("HLS stream ingested successfully", [
            'channel_id' => $channel->id,
            'stream_url' => $streamUrl
        ]);

        return true;
    }

    /**
     * Validate an HLS stream by checking its manifest
     *
     * @param string $streamUrl
     * @return bool
     */
    protected function validateHlsStream(string $streamUrl): bool
    {
        try {
            // Use FFprobe to validate the HLS stream
            $process = new Process([
                'ffprobe',
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                $streamUrl
            ]);

            $process->setTimeout(10);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("Failed to validate HLS stream", [
                    'url' => $streamUrl,
                    'error' => $process->getErrorOutput()
                ]);
                return false;
            }

            $output = json_decode($process->getOutput(), true);
            
            // Check if we have valid stream information
            if (!isset($output['streams']) || empty($output['streams'])) {
                Log::error("No streams found in HLS manifest", [
                    'url' => $streamUrl
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Exception validating HLS stream", [
                'url' => $streamUrl,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }
}
```

## 2. Laravel API Endpoint for Channel Information

### ChannelController.php
```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChannelResource;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ChannelController extends Controller
{
    /**
     * Get a list of all channels with their current status
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $channels = Channel::with(['streamStatus', 'streamInputs'])
            ->orderBy('name')
            ->get();

        return ChannelResource::collection($channels);
    }

    /**
     * Get a specific channel with detailed information
     *
     * @param Channel $channel
     * @return ChannelResource
     */
    public function show(Channel $channel): ChannelResource
    {
        $channel->load(['streamStatus', 'streamInputs', 'streamOutputs']);
        
        return new ChannelResource($channel);
    }

    /**
     * Get the HLS stream URL for a channel
     *
     * @param Channel $channel
     * @return Response
     */
    public function getStreamUrl(Channel $channel): Response
    {
        $hlsInput = $channel->streamInputs()
            ->where('type', 'hls')
            ->where('active', true)
            ->first();

        if (!$hlsInput) {
            return response([
                'message' => 'No active HLS stream available for this channel'
            ], 404);
        }

        return response([
            'stream_url' => $hlsInput->url,
            'is_online' => $channel->streamStatus->is_online ?? false
        ]);
    }

    /**
     * Get the latest thumbnail for a channel
     *
     * @param Channel $channel
     * @return Response
     */
    public function getThumbnail(Channel $channel): Response
    {
        $thumbnailPath = storage_path("app/public/thumbnails/{$channel->id}/latest.jpg");
        
        if (!file_exists($thumbnailPath)) {
            return response([
                'message' => 'Thumbnail not available'
            ], 404);
        }

        return response()->file($thumbnailPath);
    }
}
```

### ChannelResource.php
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $hlsInput = $this->streamInputs->where('type', 'hls')->where('active', true)->first();
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logo_url' => $this->logo_url ? asset("storage/logos/{$this->logo_url}") : null,
            'thumbnail_url' => asset("storage/thumbnails/{$this->id}/latest.jpg"),
            'stream_url' => $hlsInput ? $hlsInput->url : null,
            'status' => $this->streamStatus->status ?? 'unknown',
            'is_online' => $this->streamStatus->is_online ?? false,
            'last_online' => $this->streamStatus->last_online,
            'bitrate' => $this->streamStatus->bitrate ?? 0,
            'details' => $this->when($request->routeIs('channels.show'), function () {
                return [
                    'resolution' => $this->streamStatus->details['resolution'] ?? null,
                    'codec' => $this->streamStatus->details['codec'] ?? null,
                    'frame_rate' => $this->streamStatus->details['frame_rate'] ?? null,
                    'inputs' => $this->streamInputs->map(function ($input) {
                        return [
                            'id' => $input->id,
                            'type' => $input->type,
                            'url' => $input->url,
                            'active' => $input->active
                        ];
                    }),
                    'outputs' => $this->whenLoaded('streamOutputs', function () {
                        return $this->streamOutputs->map(function ($output) {
                            return [
                                'id' => $output->id,
                                'type' => $output->type,
                                'url' => $output->url,
                                'active' => $output->active
                            ];
                        });
                    })
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
```

## 3. Vue.js Frontend: Display HLS Stream

### api.js
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add a request interceptor for authentication
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Add a response interceptor for error handling
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Handle unauthorized access
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  // Channel endpoints
  getChannels() {
    return api.get('/channels');
  },
  
  getChannel(id) {
    return api.get(`/channels/${id}`);
  },
  
  getStreamUrl(id) {
    return api.get(`/channels/${id}/stream-url`);
  },
  
  // Stream management endpoints
  startStream(id) {
    return api.post(`/streams/${id}/start`);
  },
  
  stopStream(id) {
    return api.post(`/streams/${id}/stop`);
  },
  
  // Monitoring endpoints
  getStreamStatus(id) {
    return api.get(`/monitoring/${id}/status`);
  },
  
  getAllStreamStatuses() {
    return api.get('/monitoring/statuses');
  }
};
```

### channelService.js
```javascript
import api from './api';

export default {
  /**
   * Fetch all channels with their status
   * @returns {Promise}
   */
  async fetchChannels() {
    try {
      const response = await api.getChannels();
      return response.data.data;
    } catch (error) {
      console.error('Error fetching channels:', error);
      throw error;
    }
  },
  
  /**
   * Fetch a single channel with detailed information
   * @param {number} id - Channel ID
   * @returns {Promise}
   */
  async fetchChannel(id) {
    try {
      const response = await api.getChannel(id);
      return response.data.data;
    } catch (error) {
      console.error(`Error fetching channel ${id}:`, error);
      throw error;
    }
  },
  
  /**
   * Get the HLS stream URL for a channel
   * @param {number} id - Channel ID
   * @returns {Promise<string>}
   */
  async getStreamUrl(id) {
    try {
      const response = await api.getStreamUrl(id);
      return response.data.stream_url;
    } catch (error) {
      console.error(`Error getting stream URL for channel ${id}:`, error);
      throw error;
    }
  },
  
  /**
   * Start streaming a channel
   * @param {number} id - Channel ID
   * @returns {Promise}
   */
  async startStream(id) {
    try {
      const response = await api.startStream(id);
      return response.data;
    } catch (error) {
      console.error(`Error starting stream for channel ${id}:`, error);
      throw error;
    }
  },
  
  /**
   * Stop streaming a channel
   * @param {number} id - Channel ID
   * @returns {Promise}
   */
  async stopStream(id) {
    try {
      const response = await api.stopStream(id);
      return response.data;
    } catch (error) {
      console.error(`Error stopping stream for channel ${id}:`, error);
      throw error;
    }
  }
};
```

## 4. Laravel WebSocket Broadcasting

### StreamStatusChanged.php (Event)
```php
<?php

namespace App\Events;

use App\Models\Channel;
use App\Models\StreamStatus;
use Illuminate\Broadcasting\Channel as BroadcastChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $status;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param StreamStatus $status
     */
    public function __construct(Channel $channel, StreamStatus $status)
    {
        $this->channel = $channel;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return BroadcastChannel|array
     */
    public function broadcastOn(): BroadcastChannel|array
    {
        return new BroadcastChannel('stream-status');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'channel_id' => $this->channel->id,
            'name' => $this->channel->name,
            'status' => $this->status->status,
            'is_online' => $this->status->is_online,
            'last_online' => $this->status->last_online,
            'bitrate' => $this->status->bitrate,
            'timestamp' => now()->toIso8601String()
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'StreamStatusChanged';
    }
}
```

### channels.php (Broadcasting Routes)
```php
<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('stream-status', function ($user) {
    return true; // Public channel, anyone can listen
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

## 5. FFmpeg Integration for Stream Processing

### FFmpegService.php
```php
<?php

namespace App\Services\StreamProcessing;

use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FFmpegService
{
    /**
     * Generate a thumbnail from an HLS stream
     *
     * @param Channel $channel
     * @param string $streamUrl
     * @return string|null Path to the generated thumbnail
     */
    public function generateThumbnail(Channel $channel, string $streamUrl): ?string
    {
        $outputDir = storage_path("app/public/thumbnails/{$channel->id}");
        $outputPath = "{$outputDir}/latest.jpg";
        
        // Ensure the directory exists
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        try {
            $process = new Process([
                'ffmpeg',
                '-y',                   // Overwrite output files
                '-i', $streamUrl,       // Input stream
                '-ss', '00:00:01',      // Seek to 1 second
                '-vframes', '1',        // Extract 1 frame
                '-q:v', '2',            // Quality level (lower is better)
                $outputPath             // Output path
            ]);
            
            $process->setTimeout(30);
            $process->run();
            
            if (!$process->isSuccessful()) {
                Log::error("Failed to generate thumbnail", [
                    'channel_id' => $channel->id,
                    'error' => $process->getErrorOutput()
                ]);
                return null;
            }
            
            return $outputPath;
        } catch (\Exception $e) {
            Log::error("Exception generating thumbnail", [
                'channel_id' => $channel->id,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Convert an HLS stream to UDP output
     *
     * @param string $inputUrl
     * @param string $outputUrl
     * @param array $options
     * @return Process
     */
    public function hlsToUdp(string $inputUrl, string $outputUrl, array $options = []): Process
    {
        $args = [
            'ffmpeg',
            '-y',                       // Overwrite output files
            '-i', $inputUrl,            // Input stream
            '-c:v', 'copy',             // Copy video codec
            '-c:a', 'copy',             // Copy audio codec
            '-f', 'mpegts',             // Output format
            '-muxdelay', '0',           // Reduce latency
        ];
        
        // Add custom options
        if (isset($options['bitrate'])) {
            $args = array_merge($args, ['-b:v', $options['bitrate']]);
        }
        
        // Add output URL
        $args[] = $outputUrl;
        
        $process = new Process($args);
        $process->setTimeout(null); // No timeout for long-running process
        $process->start();
        
        return $process;
    }
    
    /**
     * Get stream information using FFprobe
     *
     * @param string $streamUrl
     * @return array|null
     */
    public function getStreamInfo(string $streamUrl): ?array
    {
        try {
            $process = new Process([
                'ffprobe',
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                $streamUrl
            ]);
            
            $process->setTimeout(10);
            $process->run();
            
            if (!$process->isSuccessful()) {
                Log::error("Failed to get stream info", [
                    'url' => $streamUrl,
                    'error' => $process->getErrorOutput()
                ]);
                return null;
            }
            
            $output = json_decode($process->getOutput(), true);
            
            // Extract relevant information
            $info = [
                'format' => $output['format']['format_name'] ?? null,
                'duration' => $output['format']['duration'] ?? null,
                'bit_rate' => $output['format']['bit_rate'] ?? null,
                'streams' => []
            ];
            
            foreach ($output['streams'] as $stream) {
                $streamInfo = [
                    'codec_type' => $stream['codec_type'] ?? null,
                    'codec_name' => $stream['codec_name'] ?? null,
                ];
                
                if ($stream['codec_type'] === 'video') {
                    $streamInfo['width'] = $stream['width'] ?? null;
                    $streamInfo['height'] = $stream['height'] ?? null;
                    $streamInfo['frame_rate'] = eval('return ' . ($stream['r_frame_rate'] ?? '0/1')) . ';';
                }
                
                $info['streams'][] = $streamInfo;
            }
            
            return $info;
        } catch (\Exception $e) {
            Log::error("Exception getting stream info", [
                'url' => $streamUrl,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }
} 