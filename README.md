# TV Monitor System

A comprehensive streaming management system for monitoring and controlling multiple video streams.

## Overview

The TV Monitor System is a robust web application designed for broadcasting and professional video environments. It provides tools for managing input streams, creating multiview layouts, monitoring stream health, and distributing output streams.

## System Requirements

- PHP 8.3.x
- Laravel 12.x
- MySQL 8.0+ or MariaDB 10.5+
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Minimum 4GB RAM, 2 CPU cores recommended
- 20GB storage space

## Installation

### 1. Server Setup

```bash
# Clone the repository
git clone https://github.com/your-organization/tvmonitor.git
cd tvmonitor

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
npm run build
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit the `.env` file to configure your database and other settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tvmonitor
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Database Setup

```bash
# Run database migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed
```

### 4. Install Demo Data (Optional)

```bash
# Install demo streams and layouts
php artisan tvmonitor:install-demo
```

### 5. Start the Application

```bash
# Start the development server
php artisan serve

# In a separate terminal, run the queue worker
php artisan queue:work
```

The application will be available at: http://localhost:8000

## Basic Configuration

### Stream Management

1. **Input Streams**: Configure your video sources
   - Navigate to Input Streams in the main menu
   - Add streams with appropriate URLs (RTMP, HLS, RTSP, etc.)
   - Test connectivity by previewing streams

2. **Output Streams**: Configure distribution streams
   - Set up output formats and destinations
   - Configure transcoding settings if needed

3. **Multiview Layouts**: Create monitoring layouts
   - Design grid layouts with multiple streams
   - Configure labels and indicators
   - Set up audio monitoring preferences

## Key Features

### Stream Health Monitoring

- Real-time status indicators for all streams
- Performance metrics (bitrate, frame rate, packet loss)
- Configurable alerts for stream issues
- Historical data for trend analysis

### Multiview Layouts

- Multiple layout templates (2×2, 3×3, PiP, T-Bar)
- Custom positioning and sizing of stream windows
- Dynamic text overlays and tally indicators
- Audio monitoring options

### Stream Processing

- Transcoding between formats
- Resolution and bitrate adjustment
- Recording and archiving
- Stream distribution to multiple endpoints

## Advanced Configuration

### Performance Optimization

- Enable hardware acceleration in Settings > System
- Adjust buffer settings for low-latency requirements
- Configure stream caching for improved performance
- Set resource limits per stream for stability

### Security Settings

- User account management with role-based access
- API token management for integrations
- Stream authentication and encryption options
- Audit logging for security monitoring

## Real-time Features

### Audio Level Monitoring

The TV Monitor System includes sophisticated audio level monitoring:

- Visual volume meters for each stream
- Audio peaks detection and visualization
- Configurable sensitivity and display options
- Audio-video synchronization for accurate monitoring

### Motion Detection

The system includes built-in motion detection features:

- Visual motion indicators on stream displays
- Configurable sensitivity and thresholds
- Event logging for motion detection
- Automatic highlighting of streams with motion

## Troubleshooting

- **Stream Connection Issues**: Verify network connectivity and URL formatting
- **Performance Problems**: Check server resources and reduce stream count/quality if needed
- **Buffering Issues**: Adjust buffer settings in the advanced stream configuration
- **Dashboard Errors**: Clear browser cache or run `php artisan view:clear` on the server

## Support Resources

- Documentation: Complete guides available in the Help menu
- Logs: Check storage/logs/laravel.log for detailed error information
- Community Forum: Visit forum.tvmonitor.example.com for community support
- Email Support: Contact support@tvmonitor.example.com for direct assistance

## Detailed Guides

The TV Monitor System includes comprehensive in-application guides:

- Stream Management Guide
- Demo Data Guide
- Output Stream Configuration Guide
- Input Stream Management
- Multiview Layout Configuration
- Stream Health Monitoring

Access these guides through the Help menu in the application.

## License

This software is licensed under the [MIT License](LICENSE.md).

## Credits

Developed and maintained by Your Organization.

For more information, please visit [example.com/tvmonitor](https://example.com/tvmonitor).
# tvmonitor
