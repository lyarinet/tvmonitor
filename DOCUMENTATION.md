# IP & OTT Multiviewer Documentation

## Architecture Overview

The IP & OTT Multiviewer is built on Laravel and uses FFmpeg for video processing. The application follows a modular architecture with the following components:

### Core Components

1. **Models**: Represent the data structure of the application
   - `InputStream`: Represents an input video stream
   - `OutputStream`: Represents an output video stream
   - `MultiviewLayout`: Represents a layout configuration for the multiview
   - `LayoutPosition`: Represents a position in a multiview layout

2. **Services**: Handle business logic and external interactions
   - `FFmpegService`: Handles FFmpeg command generation and execution
   - `StreamMonitoringService`: Monitors stream health and generates thumbnails

3. **Jobs**: Handle asynchronous processing
   - `ProcessMultiviewStream`: Starts or stops a multiview process
   - `MonitorStreamHealth`: Checks the health of streams
   - `GenerateThumbnails`: Generates thumbnails for input streams

4. **Commands**: Provide CLI interfaces for common tasks
   - `MonitorStreams`: Monitors stream health
   - `GenerateStreamThumbnails`: Generates thumbnails for streams
   - `ManageMultiview`: Manages multiview processes

5. **Admin Interface**: Provides a web interface for managing the application
   - Built with Filament, a TALL stack admin panel for Laravel
   - Includes resources for managing input streams, output streams, and layouts
   - Provides dashboards for monitoring stream health

## Database Schema

### input_streams
- `id`: Primary key
- `name`: Stream name
- `protocol`: Stream protocol (HLS, HTTP, DASH, RTSP, UDP)
- `url`: Stream URL
- `username`: Authentication username (optional)
- `password`: Authentication password (optional)
- `status`: Stream status (active, inactive, error)
- `metadata`: Additional stream information (JSON)
- `error_log`: Error log (JSON)
- `created_at`, `updated_at`: Timestamps

### multiview_layouts
- `id`: Primary key
- `name`: Layout name
- `description`: Layout description
- `rows`: Number of rows in the grid
- `columns`: Number of columns in the grid
- `width`: Layout width in pixels
- `height`: Layout height in pixels
- `background_color`: Background color
- `status`: Layout status (active, inactive)
- `metadata`: Additional layout information (JSON)
- `created_at`, `updated_at`: Timestamps

### layout_positions
- `id`: Primary key
- `multiview_layout_id`: Foreign key to multiview_layouts
- `input_stream_id`: Foreign key to input_streams (optional)
- `position_x`: X position in pixels
- `position_y`: Y position in pixels
- `width`: Width in pixels
- `height`: Height in pixels
- `z_index`: Z-index for overlapping positions
- `show_label`: Whether to show a label
- `label_position`: Label position (top, bottom, left, right)
- `overlay_options`: Additional overlay options (JSON)
- `created_at`, `updated_at`: Timestamps

### output_streams
- `id`: Primary key
- `name`: Stream name
- `protocol`: Stream protocol (HLS, HTTP, DASH, RTSP, UDP)
- `url`: Stream URL
- `multiview_layout_id`: Foreign key to multiview_layouts
- `status`: Stream status (active, inactive, error)
- `ffmpeg_options`: Additional FFmpeg options (JSON)
- `metadata`: Additional stream information (JSON)
- `error_log`: Error log (JSON)
- `created_at`, `updated_at`: Timestamps

## FFmpeg Integration

The application uses FFmpeg for video processing. The `FFmpegService` class handles the generation and execution of FFmpeg commands.

### FFmpeg Command Generation

The `generateMultiviewCommand` method in `FFmpegService` generates an FFmpeg command for a multiview layout. The command includes:

1. Input streams for each position in the layout
2. A filter complex for arranging the streams in a grid
3. Output options based on the output stream protocol

### FFmpeg Process Management

The application uses Laravel's Process facade to start and stop FFmpeg processes. The processes run in the background and are managed by the `ProcessMultiviewStream` job.

## Stream Health Monitoring

The application monitors the health of input and output streams using the `StreamMonitoringService` class.

### Input Stream Monitoring

The `monitorInputStream` method checks the health of an input stream using FFmpeg's `ffprobe` command. It retrieves information such as:

- Resolution
- Codec
- Bitrate
- Duration

### Output Stream Monitoring

The `monitorOutputStream` method checks if an output stream's FFmpeg process is still running. It uses the process ID stored in the output stream's metadata.

## Thumbnail Generation

The application generates thumbnails for input streams using the `generateThumbnail` method in `FFmpegService`. The thumbnails are stored in the `storage/app/public/thumbnails` directory and are accessible through the `storage` symbolic link.

## Scheduled Tasks

The application includes scheduled tasks for monitoring stream health and generating thumbnails. These tasks are defined in the `schedule` method of the `Console\Kernel` class.

## Queue System

The application uses Laravel's queue system for handling long-running tasks such as starting FFmpeg processes and monitoring stream health. The queue can be configured to use Redis, database, or other drivers.

## Error Handling

The application includes comprehensive error handling for FFmpeg processes and stream monitoring. Errors are logged in the `error_log` field of the respective models and are displayed in the admin interface.

## Security Considerations

### Authentication

The application uses Laravel's built-in authentication system. Access to the admin interface is restricted to authenticated users.

### Stream Credentials

Stream credentials (username and password) are stored in the database. The password field is not encrypted, so it's important to secure the database.

### FFmpeg Process Security

FFmpeg processes are started with the same user as the web server. It's important to ensure that this user has the necessary permissions to access the input and output streams.

## Performance Considerations

### FFmpeg Resource Usage

FFmpeg processes can be resource-intensive, especially when processing multiple high-resolution streams. It's important to monitor the server's CPU and memory usage.

### Queue Worker Configuration

The queue worker should be configured to handle the expected load. For production environments, it's recommended to use Supervisor to manage the queue workers.

### Database Optimization

The application uses JSON fields for storing metadata and error logs. These fields can grow large over time, so it's important to monitor the database size and performance.

## Deployment Considerations

### Server Requirements

- PHP 8.2 or higher
- FFmpeg installed on the server
- MySQL/PostgreSQL database
- Redis for queue processing (optional but recommended)

### Web Server Configuration

The application can be deployed on Apache or Nginx. For Nginx, the following configuration is recommended:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Queue Worker Configuration

For production environments, it's recommended to use Supervisor to manage the queue workers. The following configuration is recommended:

```ini
[program:multiviewer-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

### Scheduled Task Configuration

For scheduled tasks, add the following Cron entry to your server:

```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Common Issues

1. **FFmpeg not installed**: Ensure FFmpeg is installed and accessible in the system PATH.
2. **Stream URL not accessible**: Verify that the stream URL is accessible from the server.
3. **Queue worker not running**: Ensure the queue worker is running and properly configured.
4. **Permission issues**: Ensure the web server user has the necessary permissions to access the input and output streams.

### Debugging

1. **Check the error logs**: The application logs errors in the `error_log` field of the respective models.
2. **Check the Laravel logs**: Laravel logs are stored in the `storage/logs` directory.
3. **Check the queue worker logs**: Queue worker logs are stored in the location specified in the Supervisor configuration.
4. **Test FFmpeg commands manually**: Run FFmpeg commands manually to verify they work. 