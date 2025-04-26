# Deployment Considerations

## Infrastructure Requirements

### Hardware Recommendations

1. **CPU**: 
   - Minimum: 8 cores
   - Recommended: 16+ cores
   - The system will be processing multiple video streams simultaneously, which is CPU-intensive.

2. **Memory**:
   - Minimum: 16GB RAM
   - Recommended: 32GB+ RAM
   - Each stream buffer requires memory, and with 60 channels, memory usage adds up quickly.

3. **Storage**:
   - SSD storage for the application and database
   - High-capacity storage for recordings (if implemented)
   - Minimum 500GB for the system, plus additional storage based on recording needs

4. **Network**:
   - Gigabit Ethernet minimum
   - Dedicated network interface for stream ingestion
   - Sufficient bandwidth to handle all incoming and outgoing streams

### Server Architecture

#### Option 1: Single Server (Small Scale)
For smaller deployments (up to 20 channels):
- One server handling both stream processing and web application
- Docker containers for isolation and resource management

#### Option 2: Distributed Architecture (Recommended for 60 Channels)
- **Application Server**: Runs Laravel backend and Vue.js frontend
- **Stream Processing Servers**: Multiple servers dedicated to FFmpeg processing
- **Database Server**: Dedicated MySQL/PostgreSQL server
- **Redis Server**: For caching, queues, and WebSockets
- **Load Balancer**: Distributes traffic across application servers

## Containerization with Docker

### Docker Compose Setup

```yaml
version: '3.8'

services:
  # Nginx web server
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/conf.d:/etc/nginx/conf.d
      - ./nginx/ssl:/etc/nginx/ssl
      - ./storage/app/public:/var/www/html/storage/app/public
    depends_on:
      - app
    networks:
      - app-network

  # Laravel application
  app:
    build:
      context: ./
      dockerfile: Dockerfile
    volumes:
      - ./:/var/www/html
      - ./storage:/var/www/html/storage
    depends_on:
      - mysql
      - redis
    networks:
      - app-network

  # Stream processing service
  stream-processor:
    build:
      context: ./
      dockerfile: Dockerfile.ffmpeg
    volumes:
      - ./storage:/var/www/html/storage
    deploy:
      replicas: 3
    networks:
      - app-network

  # Queue worker
  queue:
    build:
      context: ./
      dockerfile: Dockerfile
    command: php artisan queue:work --tries=3
    volumes:
      - ./:/var/www/html
    depends_on:
      - mysql
      - redis
    networks:
      - app-network

  # WebSocket server
  websocket:
    build:
      context: ./
      dockerfile: Dockerfile
    command: php artisan websockets:serve
    ports:
      - "6001:6001"
    volumes:
      - ./:/var/www/html
    depends_on:
      - redis
    networks:
      - app-network

  # MySQL database
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - app-network

  # Redis for caching and queues
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  mysql-data:
  redis-data:
```

### Dockerfile for Laravel Application

```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
```

### Dockerfile for FFmpeg Processing

```dockerfile
FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    ffmpeg \
    libavcodec-extra \
    libavformat-dev \
    libavutil-dev \
    libswscale-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage

# Run stream processing command
CMD ["php", "artisan", "streams:process-all"]
```

## Cloud Deployment Options

### AWS Architecture

1. **EC2 Instances**:
   - Application servers: t3.xlarge or c5.2xlarge
   - Stream processing: c5.4xlarge (compute-optimized)

2. **Load Balancing**:
   - Application Load Balancer for HTTP/HTTPS traffic
   - Network Load Balancer for WebSocket connections

3. **Auto Scaling**:
   - Scale stream processing instances based on number of active channels
   - Scale application servers based on user load

4. **Managed Services**:
   - RDS for MySQL/PostgreSQL
   - ElastiCache for Redis
   - S3 for thumbnail and recording storage

### Google Cloud Platform

1. **Compute Engine**:
   - Application servers: n2-standard-4
   - Stream processing: c2-standard-8 (compute-optimized)

2. **Managed Services**:
   - Cloud SQL for MySQL/PostgreSQL
   - Memorystore for Redis
   - Cloud Storage for media files

### Kubernetes Deployment

For larger, more complex deployments, Kubernetes offers better scalability:

1. **Benefits**:
   - Automatic scaling of stream processing pods
   - Self-healing infrastructure
   - Rolling updates with zero downtime

2. **Components**:
   - Nginx Ingress Controller for routing
   - StatefulSets for database and Redis
   - Deployments for application and stream processors
   - Persistent Volumes for storage

## Performance Optimization

### Nginx Configuration

```nginx
# /etc/nginx/conf.d/default.conf
server {
    listen 80;
    server_name your-domain.com;
    
    # Redirect to HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_session_cache shared:SSL:10m;
    
    # HLS streaming configuration
    location /hls {
        root /var/www/html/storage/app/public;
        add_header Cache-Control no-cache;
        add_header Access-Control-Allow-Origin *;
        
        # CORS headers
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' '*';
            add_header 'Access-Control-Allow-Methods' 'GET, OPTIONS';
            add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range';
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            return 204;
        }
        
        types {
            application/vnd.apple.mpegurl m3u8;
            video/mp2t ts;
        }
        
        # Disable cache for m3u8 files
        location ~ \.m3u8$ {
            add_header Cache-Control no-cache;
        }
        
        # Cache TS fragments
        location ~ \.ts$ {
            add_header Cache-Control max-age=3600;
        }
    }
    
    # Laravel application
    location / {
        root /var/www/html/public;
        index index.php;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffering off;
    }
    
    # WebSocket proxy
    location /app {
        proxy_pass http://websocket:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
    
    # Static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, max-age=2592000";
    }
}
```

## Monitoring and Maintenance

### Monitoring Tools

1. **Prometheus & Grafana**:
   - Monitor system resources (CPU, memory, disk, network)
   - Track stream health metrics
   - Set up alerts for offline streams or system issues

2. **Laravel Telescope**:
   - Debug API requests
   - Monitor queue processing
   - Track exceptions

3. **Laravel Horizon**:
   - Monitor queue workers
   - Track job processing times
   - Manage failed jobs

### Backup Strategy

1. **Database Backups**:
   - Daily full backups
   - Hourly incremental backups
   - Retention policy: 7 days for hourly, 30 days for daily

2. **Configuration Backups**:
   - Version control for configuration files
   - Automated deployment with CI/CD

3. **Media Backups**:
   - Backup thumbnails and important recordings
   - Consider using object storage with versioning

### Scaling Considerations

1. **Horizontal Scaling**:
   - Add more stream processing servers as channel count increases
   - Use load balancing to distribute traffic

2. **Vertical Scaling**:
   - Upgrade CPU and memory for existing servers
   - Particularly important for stream processing servers

3. **Database Scaling**:
   - Consider read replicas for high-traffic deployments
   - Implement database sharding for very large installations 