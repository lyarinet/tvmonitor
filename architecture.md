# TV Channel Monitoring System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           Client Browser                                │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │                       Vue.js Frontend                           │    │
│  │                                                                 │    │
│  │  ┌─────────────┐   ┌─────────────┐   ┌─────────────────────┐    │    │
│  │  │ Channel Grid │  │ Video.js/   │   │ WebSocket Client    │    │    │
│  │  │ Component   │   │ hls.js      │   │ (Stream Status)     │    │    │
│  │  └─────────────┘   └─────────────┘   └─────────────────────┘    │    │
│  │                                                                 │    │
│  └─────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │
                                    │ HTTP/WebSocket
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           Nginx Web Server                              │
│  ┌─────────────────────────┐   ┌─────────────────────────────────────┐  │
│  │ Static Asset Serving    │   │ Reverse Proxy for Laravel & Streams │  │
│  └─────────────────────────┘   └─────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         Laravel Backend                                 │
│                                                                         │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌─────────────┐  │
│  │ API         │   │ Channel     │   │ Stream      │   │ WebSocket   │  │
│  │ Controllers │   │ Management  │   │ Processing  │   │ Server      │  │
│  └─────────────┘   └─────────────┘   └─────────────┘   └─────────────┘  │
│                                                                         │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌─────────────┐  │
│  │ Stream      │   │ Monitoring  │   │ Queue       │   │ Event       │  │
│  │ Ingest      │   │ & Logging   │   │ Workers     │   │ Broadcasting│  │
│  └─────────────┘   └─────────────┘   └─────────────┘   └─────────────┘  │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
                ▲                   ▲                   ▲
                │                   │                   │
                ▼                   ▼                   ▼
┌───────────────────┐   ┌─────────────────────┐   ┌───────────────────────┐
│ MySQL/PostgreSQL  │   │ Redis               │   │ FFmpeg/Media Server   │
│ ┌─────────────┐   │   │ ┌─────────────────┐ │   │ ┌─────────────────┐   │
│ │ Channel     │   │   │ │ Cache & Queues  │ │   │ │ Stream Handling │   │
│ │ Config      │   │   │ │                 │ │   │ │ & Processing    │   │
│ └─────────────┘   │   │ └─────────────────┘ │   │ └─────────────────┘   │
│ ┌─────────────┐   │   │ ┌─────────────────┐ │   │                       │
│ │ Stream      │   │   │ │ WebSocket Data  │ │   │                       │
│ │ Status      │   │   │ │                 │ │   │                       │
│ └─────────────┘   │   │ └─────────────────┘ │   │                       │
└───────────────────┘   └─────────────────────┘   └───────────────────────┘
        ▲                         ▲                          ▲
        │                         │                          │
        │                         │                          │
        ▼                         ▼                          ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        Input Streams                                   │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌─────────────┐ │
│  │ UDP         │   │ TCP         │   │ HTTP        │   │ HLS         │ │
│  │ Streams     │   │ Streams     │   │ Streams     │   │ Streams     │ │
│  └─────────────┘   └─────────────┘   └─────────────┘   └─────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

## Key Components:

1. **Frontend (Vue.js)**:
   - Channel Grid Component for displaying multiple streams
   - Video.js/hls.js for stream playback
   - WebSocket client for real-time status updates

2. **Backend (Laravel)**:
   - API Controllers for channel management
   - Stream Processing for handling different input formats
   - WebSocket Server for real-time updates
   - Monitoring & Logging for stream health

3. **Infrastructure**:
   - MySQL/PostgreSQL for persistent storage
   - Redis for caching and real-time messaging
   - FFmpeg/Media Server for stream processing
   - Nginx for serving static assets and reverse proxy

4. **Input/Output Streams**:
   - Support for UDP, TCP, HTTP, and HLS inputs
   - Processing for UDP, RTSP, MP4, and HLS outputs 