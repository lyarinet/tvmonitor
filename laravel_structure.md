# Laravel Backend Structure

```
app/
├── Console/
│   └── Commands/
│       ├── CheckStreamHealth.php
│       ├── ProcessStreamInput.php
│       └── GenerateThumbnails.php
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   ├── ChannelController.php
│   │   │   ├── StreamController.php
│   │   │   └── MonitoringController.php
│   │   └── Admin/
│   │       ├── ChannelManagementController.php
│   │       └── DashboardController.php
│   ├── Middleware/
│   │   └── EnsureStreamAccess.php
│   └── Resources/
│       ├── ChannelResource.php
│       └── StreamStatusResource.php
├── Models/
│   ├── Channel.php
│   ├── StreamInput.php
│   ├── StreamOutput.php
│   └── StreamStatus.php
├── Services/
│   ├── StreamProcessing/
│   │   ├── FFmpegService.php
│   │   ├── HlsProcessor.php
│   │   ├── UdpProcessor.php
│   │   ├── TcpProcessor.php
│   │   └── HttpProcessor.php
│   ├── Monitoring/
│   │   ├── StreamHealthService.php
│   │   └── AlertService.php
│   └── Ingest/
│       ├── StreamIngestService.php
│       └── StreamValidationService.php
├── Events/
│   ├── StreamStatusChanged.php
│   ├── StreamOffline.php
│   └── StreamOnline.php
├── Listeners/
│   ├── UpdateStreamStatus.php
│   ├── NotifyStreamOffline.php
│   └── LogStreamEvent.php
├── Jobs/
│   ├── ProcessStream.php
│   ├── GenerateThumbnail.php
│   └── MonitorStreamHealth.php
└── Exceptions/
    ├── StreamProcessingException.php
    └── InvalidStreamException.php

database/
├── migrations/
│   ├── 2023_01_01_000000_create_channels_table.php
│   ├── 2023_01_01_000001_create_stream_inputs_table.php
│   ├── 2023_01_01_000002_create_stream_outputs_table.php
│   └── 2023_01_01_000003_create_stream_statuses_table.php
└── seeders/
    └── ChannelSeeder.php

routes/
├── api.php
├── channels.php
└── web.php

config/
├── broadcasting.php
├── queue.php
└── streaming.php
```

## Key Components Explanation

### Models

1. **Channel.php**
   - Represents a TV channel with name, description, logo, etc.
   - Relationships to StreamInput and StreamOutput

2. **StreamInput.php**
   - Defines input stream configuration (URL, protocol, credentials)
   - Belongs to a Channel

3. **StreamOutput.php**
   - Defines output stream configuration (format, quality, destination)
   - Belongs to a Channel

4. **StreamStatus.php**
   - Tracks the current status of a stream (online/offline, bitrate, errors)
   - Belongs to a Channel

### Controllers

1. **API/ChannelController.php**
   - RESTful API endpoints for channel information
   - Used by the frontend to get channel data

2. **API/StreamController.php**
   - Endpoints for stream management (start/stop)
   - Stream URL generation

3. **API/MonitoringController.php**
   - Endpoints for stream health and status

4. **Admin/ChannelManagementController.php**
   - Admin interface for managing channels
   - CRUD operations for channel configuration

### Services

1. **StreamProcessing/FFmpegService.php**
   - Core service for interacting with FFmpeg
   - Handles stream format conversion

2. **StreamProcessing/HlsProcessor.php**
   - Specialized processor for HLS streams
   - Handles segmentation and playlist generation

3. **Ingest/StreamIngestService.php**
   - Manages the ingestion of streams from various sources
   - Validates stream inputs

4. **Monitoring/StreamHealthService.php**
   - Monitors stream health (bitrate, frame rate, errors)
   - Triggers alerts for issues

### Jobs

1. **ProcessStream.php**
   - Queued job for processing stream inputs
   - Converts between formats as needed

2. **GenerateThumbnail.php**
   - Creates thumbnails for channel preview
   - Runs periodically to update thumbnails

3. **MonitorStreamHealth.php**
   - Scheduled job to check stream health
   - Reports issues and updates status

### Events & Listeners

1. **StreamStatusChanged.php**
   - Fired when stream status changes
   - Triggers WebSocket updates to frontend

2. **StreamOffline.php**
   - Fired when a stream goes offline
   - Triggers alerts and recovery attempts

### Console Commands

1. **CheckStreamHealth.php**
   - Scheduled command to check all stream health
   - `php artisan streams:check-health`

2. **ProcessStreamInput.php**
   - Command to manually process a stream
   - `php artisan streams:process {channel_id}` 