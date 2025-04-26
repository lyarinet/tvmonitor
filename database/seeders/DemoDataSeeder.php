<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InputStream;
use App\Models\MultiviewLayout;
use App\Models\LayoutPosition;
use App\Models\OutputStream;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to seed demo data...');
        
        // Create demo input streams
        $this->createInputStreams();
        
        // Create demo multiview layouts
        $this->createMultiviewLayouts();
        
        // Create demo output streams
        $this->createOutputStreams();
        
        $this->command->info('Demo data seeding completed successfully!');
    }
    
    /**
     * Create demo input streams
     */
    private function createInputStreams(): void
    {
        $this->command->info('Creating demo input streams...');
        
        $inputStreams = [
            // RTMP Streams
            [
                'name' => 'News Studio Camera 1',
                'url' => 'rtmp://demo.server.com/live/studio1',
                'protocol' => 'rtmp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Main camera for the news studio',
                    'location' => 'Studio A',
                    'camera_type' => 'Sony HDC-3500',
                    'resolution' => '1920x1080'
                ]),
            ],
            [
                'name' => 'News Studio Camera 2',
                'url' => 'rtmp://demo.server.com/live/studio2',
                'protocol' => 'rtmp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Wide angle camera for the news studio',
                    'location' => 'Studio A',
                    'camera_type' => 'Sony HDC-3500',
                    'resolution' => '1920x1080'
                ]),
            ],
            
            // HLS Streams
            [
                'name' => 'Weather Graphics (HLS)',
                'url' => 'https://demo.server.com/hls/weather/playlist.m3u8',
                'protocol' => 'hls',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Weather graphics system output via HLS',
                    'source' => 'Graphics System',
                    'resolution' => '1920x1080',
                    'segment_duration' => '6',
                    'playlist_type' => 'event'
                ]),
            ],
            [
                'name' => 'Live Sports (HLS)',
                'url' => 'https://demo.server.com/hls/sports/playlist.m3u8',
                'protocol' => 'hls',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Live sports feed via HLS',
                    'source' => 'Sports Provider',
                    'resolution' => '1920x1080',
                    'segment_duration' => '4',
                    'playlist_type' => 'live'
                ]),
            ],
            
            // UDP Streams
            [
                'name' => 'International News Feed',
                'url' => 'udp://239.0.0.2:1234',
                'protocol' => 'udp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'International news feed via multicast UDP',
                    'source' => 'Satellite Receiver 1',
                    'resolution' => '1920x1080',
                    'bitrate' => '8000000'
                ]),
            ],
            [
                'name' => 'Local News Feed',
                'url' => 'udp://239.0.0.3:1234',
                'protocol' => 'udp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Local news feed via multicast UDP',
                    'source' => 'Local Studio',
                    'resolution' => '1920x1080',
                    'bitrate' => '6000000'
                ]),
            ],
            
            // RTSP Streams
            [
                'name' => 'Security Camera 1',
                'url' => 'rtsp://admin:password@192.168.1.100:554/stream1',
                'protocol' => 'rtsp',
                'username' => 'admin',
                'password' => 'password',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Security camera in lobby via RTSP',
                    'location' => 'Building Lobby',
                    'camera_type' => 'Axis P3245-LV',
                    'resolution' => '1280x720'
                ]),
            ],
            [
                'name' => 'Security Camera 2',
                'url' => 'rtsp://admin:password@192.168.1.101:554/stream1',
                'protocol' => 'rtsp',
                'username' => 'admin',
                'password' => 'password',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Security camera in parking lot via RTSP',
                    'location' => 'Parking Lot',
                    'camera_type' => 'Axis P3245-LV',
                    'resolution' => '1280x720'
                ]),
            ],
            
            // HTTP Streams
            [
                'name' => 'VOD Content 1',
                'url' => 'http://demo.server.com/vod/movie1.mp4',
                'protocol' => 'http',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Video on demand content via HTTP',
                    'content_type' => 'Movie',
                    'duration' => '01:45:30',
                    'resolution' => '1920x1080'
                ]),
            ],
            [
                'name' => 'VOD Content 2',
                'url' => 'http://demo.server.com/vod/movie2.mp4',
                'protocol' => 'http',
                'status' => 'inactive',
                'metadata' => json_encode([
                    'description' => 'Video on demand content via HTTP',
                    'content_type' => 'TV Show',
                    'duration' => '00:45:00',
                    'resolution' => '1920x1080'
                ]),
            ],
            
            // DASH Streams
            [
                'name' => 'Live Event (DASH)',
                'url' => 'https://demo.server.com/dash/event/manifest.mpd',
                'protocol' => 'dash',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Live event stream via DASH',
                    'source' => 'Event Venue',
                    'resolution' => '1920x1080',
                    'segment_duration' => '2',
                    'profile' => 'live'
                ]),
            ],
            [
                'name' => 'On-Demand Content (DASH)',
                'url' => 'https://demo.server.com/dash/vod/manifest.mpd',
                'protocol' => 'dash',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'On-demand content via DASH',
                    'content_type' => 'Documentary',
                    'duration' => '01:30:00',
                    'resolution' => '1920x1080',
                    'profile' => 'on-demand'
                ]),
            ],
            
            // Additional streams
            [
                'name' => 'Studio Clock',
                'url' => 'rtmp://demo.server.com/live/clock',
                'protocol' => 'rtmp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Studio clock and timer',
                    'source' => 'Clock Generator',
                    'resolution' => '1280x720'
                ]),
            ],
            [
                'name' => 'Test Pattern',
                'url' => 'rtmp://demo.server.com/live/testpattern',
                'protocol' => 'rtmp',
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Color bars test pattern',
                    'source' => 'Test Generator',
                    'resolution' => '1920x1080'
                ]),
            ],
        ];
        
        foreach ($inputStreams as $streamData) {
            InputStream::create($streamData);
        }
        
        $this->command->info('Created ' . count($inputStreams) . ' demo input streams');
    }
    
    /**
     * Create demo multiview layouts
     */
    private function createMultiviewLayouts(): void
    {
        $this->command->info('Creating demo multiview layouts...');
        
        // Create a 2x2 grid layout
        $gridLayout = MultiviewLayout::create([
            'name' => 'Standard 2x2 Grid',
            'description' => 'Standard 2x2 grid layout for monitoring',
            'rows' => 2,
            'columns' => 2,
            'width' => 1920,
            'height' => 1080,
            'background_color' => '#000000',
            'status' => 'active',
            'metadata' => json_encode([
                'created_by' => 'system',
                'template' => 'grid'
            ]),
        ]);
        
        // Create positions for the 2x2 grid
        $cellWidth = floor($gridLayout->width / $gridLayout->columns);
        $cellHeight = floor($gridLayout->height / $gridLayout->rows);
        
        // Top-left position
        LayoutPosition::create([
            'multiview_layout_id' => $gridLayout->id,
            'input_stream_id' => 1, // News Studio Camera 1
            'position_x' => 0,
            'position_y' => 0,
            'width' => $cellWidth,
            'height' => $cellHeight,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Top-right position
        LayoutPosition::create([
            'multiview_layout_id' => $gridLayout->id,
            'input_stream_id' => 2, // News Studio Camera 2
            'position_x' => $cellWidth,
            'position_y' => 0,
            'width' => $cellWidth,
            'height' => $cellHeight,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Bottom-left position
        LayoutPosition::create([
            'multiview_layout_id' => $gridLayout->id,
            'input_stream_id' => 3, // Weather Graphics (HLS)
            'position_x' => 0,
            'position_y' => $cellHeight,
            'width' => $cellWidth,
            'height' => $cellHeight,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Bottom-right position
        LayoutPosition::create([
            'multiview_layout_id' => $gridLayout->id,
            'input_stream_id' => 4, // Live Sports (HLS)
            'position_x' => $cellWidth,
            'position_y' => $cellHeight,
            'width' => $cellWidth,
            'height' => $cellHeight,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Create a 3x3 grid layout
        $largeGridLayout = MultiviewLayout::create([
            'name' => 'Large 3x3 Grid',
            'description' => 'Larger 3x3 grid layout for monitoring multiple streams',
            'rows' => 3,
            'columns' => 3,
            'width' => 1920,
            'height' => 1080,
            'background_color' => '#000000',
            'status' => 'active',
            'metadata' => json_encode([
                'created_by' => 'system',
                'template' => 'grid'
            ]),
        ]);
        
        // Calculate cell dimensions for 3x3 grid
        $cellWidth3x3 = floor($largeGridLayout->width / $largeGridLayout->columns);
        $cellHeight3x3 = floor($largeGridLayout->height / $largeGridLayout->rows);
        
        // Create positions for the 3x3 grid (9 positions)
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 3; $col++) {
                $streamId = $row * 3 + $col + 1; // Use streams 1-9
                if ($streamId > 14) $streamId = 14; // Fallback to test pattern if we run out of streams
                
                LayoutPosition::create([
                    'multiview_layout_id' => $largeGridLayout->id,
                    'input_stream_id' => $streamId,
                    'position_x' => $col * $cellWidth3x3,
                    'position_y' => $row * $cellHeight3x3,
                    'width' => $cellWidth3x3,
                    'height' => $cellHeight3x3,
                    'z_index' => 0,
                    'show_label' => true,
                    'label_position' => 'bottom',
                ]);
            }
        }
        
        // Create a program/preview layout
        $programPreviewLayout = MultiviewLayout::create([
            'name' => 'Program & Preview',
            'description' => 'Program and preview layout with smaller sources',
            'rows' => 3,
            'columns' => 3,
            'width' => 1920,
            'height' => 1080,
            'background_color' => '#000000',
            'status' => 'active',
            'metadata' => json_encode([
                'created_by' => 'system',
                'template' => 'custom'
            ]),
        ]);
        
        // Program (large top area)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 1, // News Studio Camera 1
            'position_x' => 0,
            'position_y' => 0,
            'width' => 1920,
            'height' => 540,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'top',
            'overlay_options' => json_encode([
                'border' => 'red',
                'border_width' => 2,
                'label_text' => 'PROGRAM'
            ]),
        ]);
        
        // Preview (middle left)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 2, // News Studio Camera 2
            'position_x' => 0,
            'position_y' => 540,
            'width' => 960,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
            'overlay_options' => json_encode([
                'border' => 'green',
                'border_width' => 2,
                'label_text' => 'PREVIEW'
            ]),
        ]);
        
        // Source 1 (middle right)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 3, // Weather Graphics (HLS)
            'position_x' => 960,
            'position_y' => 540,
            'width' => 960,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Source 2 (bottom left)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 5, // International News Feed
            'position_x' => 0,
            'position_y' => 810,
            'width' => 480,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Source 3 (bottom middle-left)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 7, // Security Camera 1
            'position_x' => 480,
            'position_y' => 810,
            'width' => 480,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Source 4 (bottom middle-right)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 11, // Live Event (DASH)
            'position_x' => 960,
            'position_y' => 810,
            'width' => 480,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        // Source 5 (bottom right)
        LayoutPosition::create([
            'multiview_layout_id' => $programPreviewLayout->id,
            'input_stream_id' => 14, // Test Pattern
            'position_x' => 1440,
            'position_y' => 810,
            'width' => 480,
            'height' => 270,
            'z_index' => 0,
            'show_label' => true,
            'label_position' => 'bottom',
        ]);
        
        $this->command->info('Created 3 demo multiview layouts with positions');
    }
    
    /**
     * Create demo output streams
     */
    private function createOutputStreams(): void
    {
        $this->command->info('Creating demo output streams...');
        
        $outputStreams = [
            // RTMP Output
            [
                'name' => 'Control Room Multiview',
                'protocol' => 'rtmp',
                'url' => 'rtmp://streaming.local/multiview/control_room',
                'multiview_layout_id' => 1, // Standard 2x2 Grid
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'Main multiview for the control room monitors',
                    'video_codec' => 'h264',
                    'video_bitrate' => 5000,
                    'audio_source_id' => 1, // News Studio Camera 1
                    'audio_codec' => 'aac',
                    'audio_bitrate' => 128,
                    'width' => 1920,
                    'height' => 1080,
                    'framerate' => 30
                ]),
                'ffmpeg_options' => json_encode([
                    'encoding_preset' => 'veryfast',
                    'keyframe_interval' => 60,
                    'pixel_format' => 'yuv420p'
                ]),
            ],
            
            // HLS Output
            [
                'name' => 'Web Streaming (HLS)',
                'protocol' => 'hls',
                'url' => '/var/www/html/hls/multiview/playlist.m3u8',
                'multiview_layout_id' => 2, // Large 3x3 Grid
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'HLS output for web streaming',
                    'video_codec' => 'h264',
                    'video_bitrate' => 3000,
                    'audio_source_id' => 1, // News Studio Camera 1
                    'audio_codec' => 'aac',
                    'audio_bitrate' => 128,
                    'width' => 1280,
                    'height' => 720,
                    'framerate' => 30,
                    'segment_duration' => 6,
                    'playlist_type' => 'event'
                ]),
                'ffmpeg_options' => json_encode([
                    'encoding_preset' => 'medium',
                    'keyframe_interval' => 60,
                    'pixel_format' => 'yuv420p',
                    'hls_time' => 6,
                    'hls_list_size' => 10,
                    'hls_flags' => 'delete_segments'
                ]),
            ],
            
            // DASH Output
            [
                'name' => 'OTT Platform (DASH)',
                'protocol' => 'dash',
                'url' => '/var/www/html/dash/multiview/manifest.mpd',
                'multiview_layout_id' => 3, // Program & Preview
                'status' => 'active',
                'metadata' => json_encode([
                    'description' => 'DASH output for OTT platforms',
                    'video_codec' => 'h264',
                    'video_bitrate' => 4000,
                    'audio_source_id' => 1, // News Studio Camera 1
                    'audio_codec' => 'aac',
                    'audio_bitrate' => 192,
                    'width' => 1920,
                    'height' => 1080,
                    'framerate' => 30,
                    'segment_duration' => 4,
                    'profile' => 'live'
                ]),
                'ffmpeg_options' => json_encode([
                    'encoding_preset' => 'medium',
                    'keyframe_interval' => 60,
                    'pixel_format' => 'yuv420p',
                    'dash_segment_type' => 'mp4',
                    'use_timeline' => 1,
                    'use_template' => 1
                ]),
            ],
            
            // UDP Output
            [
                'name' => 'Multicast Distribution',
                'protocol' => 'udp',
                'url' => 'udp://239.0.0.10:1234',
                'multiview_layout_id' => 1, // Standard 2x2 Grid
                'status' => 'inactive',
                'metadata' => json_encode([
                    'description' => 'UDP multicast for internal distribution',
                    'video_codec' => 'h264',
                    'video_bitrate' => 6000,
                    'audio_source_id' => 1, // News Studio Camera 1
                    'audio_codec' => 'aac',
                    'audio_bitrate' => 192,
                    'width' => 1920,
                    'height' => 1080,
                    'framerate' => 30,
                    'muxer' => 'mpegts'
                ]),
                'ffmpeg_options' => json_encode([
                    'encoding_preset' => 'veryfast',
                    'keyframe_interval' => 30,
                    'pixel_format' => 'yuv420p',
                    'muxer_options' => 'muxrate=8000000'
                ]),
            ],
            
            // File Output
            [
                'name' => 'Recording Multiview',
                'protocol' => 'file',
                'url' => '/var/media/recordings/multiview_%Y%m%d_%H%M%S.mp4',
                'multiview_layout_id' => 1, // Standard 2x2 Grid
                'status' => 'inactive',
                'metadata' => json_encode([
                    'description' => 'Multiview for recording to file',
                    'video_codec' => 'h264',
                    'video_bitrate' => 10000,
                    'audio_source_id' => 1, // News Studio Camera 1
                    'audio_codec' => 'aac',
                    'audio_bitrate' => 256,
                    'width' => 1920,
                    'height' => 1080,
                    'framerate' => 30
                ]),
                'ffmpeg_options' => json_encode([
                    'encoding_preset' => 'slow',
                    'keyframe_interval' => 60,
                    'pixel_format' => 'yuv420p',
                    'segment_time' => 3600 // 1 hour segments
                ]),
            ],
        ];
        
        foreach ($outputStreams as $streamData) {
            OutputStream::create($streamData);
        }
        
        $this->command->info('Created ' . count($outputStreams) . ' demo output streams');
    }
} 