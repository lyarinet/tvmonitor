# Frontend Component Structure (Vue.js)

```
src/
├── assets/
│   ├── css/
│   │   └── main.css
│   └── images/
│       └── logo.svg
├── components/
│   ├── channel/
│   │   ├── ChannelGrid.vue
│   │   ├── ChannelTile.vue
│   │   ├── ChannelControls.vue
│   │   └── ChannelStatus.vue
│   ├── player/
│   │   ├── VideoPlayer.vue
│   │   ├── HlsPlayer.vue
│   │   └── PlayerControls.vue
│   ├── layout/
│   │   ├── AppHeader.vue
│   │   ├── AppSidebar.vue
│   │   └── AppFooter.vue
│   └── common/
│       ├── StatusIndicator.vue
│       ├── LoadingSpinner.vue
│       └── ErrorMessage.vue
├── views/
│   ├── Dashboard.vue
│   ├── ChannelMonitor.vue
│   ├── ChannelDetail.vue
│   └── Settings.vue
├── services/
│   ├── api.js
│   ├── channelService.js
│   ├── streamService.js
│   └── websocketService.js
├── store/
│   ├── index.js
│   ├── modules/
│   │   ├── channels.js
│   │   ├── streams.js
│   │   └── settings.js
│   └── plugins/
│       └── websocket.js
├── router/
│   └── index.js
├── utils/
│   ├── formatters.js
│   └── streamHelpers.js
├── App.vue
└── main.js
```

## Key Components Explanation

### Channel Components

1. **ChannelGrid.vue**
   ```vue
   <template>
     <div class="channel-grid" :class="{ 'grid-compact': isCompactView }">
       <ChannelTile 
         v-for="channel in channels" 
         :key="channel.id" 
         :channel="channel"
         :class="{ 'selected': selectedChannelId === channel.id }"
         @click="selectChannel(channel.id)"
       />
     </div>
   </template>
   
   <script>
   import ChannelTile from './ChannelTile.vue';
   
   export default {
     components: { ChannelTile },
     props: {
       channels: {
         type: Array,
         required: true
       },
       isCompactView: {
         type: Boolean,
         default: false
       }
     },
     data() {
       return {
         selectedChannelId: null
       };
     },
     methods: {
       selectChannel(id) {
         this.selectedChannelId = id;
         this.$emit('channel-selected', id);
       }
     }
   };
   </script>
   
   <style scoped>
   .channel-grid {
     display: grid;
     grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
     gap: 16px;
     padding: 16px;
   }
   
   .grid-compact {
     grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
     gap: 8px;
   }
   
   @media (max-width: 768px) {
     .channel-grid {
       grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
     }
   }
   
   @media (min-width: 1920px) {
     .channel-grid {
       grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
     }
   }
   </style>
   ```

2. **ChannelTile.vue**
   ```vue
   <template>
     <div class="channel-tile" :class="{ 'offline': !channel.isOnline }">
       <div class="channel-header">
         <h3>{{ channel.name }}</h3>
         <StatusIndicator :status="channel.status" />
       </div>
       
       <div class="player-container">
         <HlsPlayer 
           v-if="channel.isOnline" 
           :stream-url="channel.streamUrl" 
           :auto-play="autoPlay"
           :muted="true"
         />
         <div v-else class="offline-placeholder">
           <img :src="channel.thumbnailUrl || defaultThumbnail" alt="Channel Thumbnail">
           <div class="offline-message">Stream Offline</div>
         </div>
       </div>
       
       <ChannelControls 
         :channel="channel"
         @volume-change="updateVolume"
         @fullscreen="toggleFullscreen"
       />
     </div>
   </template>
   
   <script>
   import HlsPlayer from '../player/HlsPlayer.vue';
   import StatusIndicator from '../common/StatusIndicator.vue';
   import ChannelControls from './ChannelControls.vue';
   
   export default {
     components: {
       HlsPlayer,
       StatusIndicator,
       ChannelControls
     },
     props: {
       channel: {
         type: Object,
         required: true
       },
       autoPlay: {
         type: Boolean,
         default: true
       }
     },
     data() {
       return {
         defaultThumbnail: require('@/assets/images/offline-placeholder.jpg'),
         volume: 0
       };
     },
     methods: {
       updateVolume(level) {
         this.volume = level;
         // Implementation for volume control
       },
       toggleFullscreen() {
         // Implementation for fullscreen toggle
       }
     }
   };
   </script>
   
   <style scoped>
   .channel-tile {
     border-radius: 8px;
     overflow: hidden;
     background-color: #1a1a1a;
     box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
     transition: transform 0.2s ease;
   }
   
   .channel-tile:hover {
     transform: translateY(-2px);
     box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
   }
   
   .channel-header {
     display: flex;
     justify-content: space-between;
     align-items: center;
     padding: 8px 12px;
     background-color: #2a2a2a;
   }
   
   .channel-header h3 {
     margin: 0;
     font-size: 14px;
     color: white;
     white-space: nowrap;
     overflow: hidden;
     text-overflow: ellipsis;
   }
   
   .player-container {
     position: relative;
     width: 100%;
     aspect-ratio: 16 / 9;
     background-color: #000;
   }
   
   .offline-placeholder {
     position: relative;
     width: 100%;
     height: 100%;
     display: flex;
     align-items: center;
     justify-content: center;
   }
   
   .offline-placeholder img {
     width: 100%;
     height: 100%;
     object-fit: cover;
     opacity: 0.5;
   }
   
   .offline-message {
     position: absolute;
     background-color: rgba(0, 0, 0, 0.7);
     color: white;
     padding: 8px 16px;
     border-radius: 4px;
     font-weight: bold;
   }
   
   .channel-tile.offline {
     opacity: 0.8;
   }
   </style>
   ```

3. **HlsPlayer.vue**
   ```vue
   <template>
     <div class="hls-player">
       <video
         ref="videoPlayer"
         class="video-js"
         :class="{ 'low-quality': lowResourceMode }"
         controls
         :muted="muted"
         :autoplay="autoPlay"
         preload="auto"
         width="100%"
         height="100%"
       ></video>
     </div>
   </template>
   
   <script>
   import videojs from 'video.js';
   import 'video.js/dist/video-js.css';
   import Hls from 'hls.js';
   
   export default {
     props: {
       streamUrl: {
         type: String,
         required: true
       },
       autoPlay: {
         type: Boolean,
         default: false
       },
       muted: {
         type: Boolean,
         default: true
       },
       lowResourceMode: {
         type: Boolean,
         default: false
       }
     },
     data() {
       return {
         player: null,
         hls: null
       };
     },
     mounted() {
       this.initializePlayer();
     },
     beforeUnmount() {
       this.destroyPlayer();
     },
     methods: {
       initializePlayer() {
         const options = {
           controls: true,
           autoplay: this.autoPlay,
           muted: this.muted,
           preload: 'auto',
           fluid: true,
           html5: {
             hls: {
               overrideNative: true
             }
           }
         };
         
         this.player = videojs(this.$refs.videoPlayer, options);
         
         if (Hls.isSupported()) {
           this.hls = new Hls({
             maxBufferLength: this.lowResourceMode ? 10 : 30,
             maxMaxBufferLength: this.lowResourceMode ? 20 : 60,
             liveSyncDuration: this.lowResourceMode ? 2 : 3
           });
           this.hls.loadSource(this.streamUrl);
           this.hls.attachMedia(this.$refs.videoPlayer);
           this.hls.on(Hls.Events.MANIFEST_PARSED, () => {
             if (this.autoPlay) {
               this.player.play().catch(error => {
                 console.warn('Autoplay prevented:', error);
               });
             }
           });
           
           this.hls.on(Hls.Events.ERROR, (event, data) => {
             if (data.fatal) {
               switch(data.type) {
                 case Hls.ErrorTypes.NETWORK_ERROR:
                   this.hls.startLoad();
                   break;
                 case Hls.ErrorTypes.MEDIA_ERROR:
                   this.hls.recoverMediaError();
                   break;
                 default:
                   this.destroyPlayer();
                   this.initializePlayer();
                   break;
               }
             }
           });
         } else if (this.$refs.videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
           // For Safari
           this.$refs.videoPlayer.src = this.streamUrl;
         }
       },
       destroyPlayer() {
         if (this.player) {
           this.player.dispose();
           this.player = null;
         }
         
         if (this.hls) {
           this.hls.destroy();
           this.hls = null;
         }
       }
     },
     watch: {
       streamUrl() {
         this.destroyPlayer();
         this.$nextTick(() => {
           this.initializePlayer();
         });
       },
       lowResourceMode() {
         this.destroyPlayer();
         this.$nextTick(() => {
           this.initializePlayer();
         });
       }
     }
   };
   </script>
   
   <style scoped>
   .hls-player {
     width: 100%;
     height: 100%;
     background-color: #000;
   }
   
   .video-js {
     width: 100%;
     height: 100%;
   }
   
   .low-quality {
     filter: brightness(0.9) contrast(0.9);
   }
   
   /* Minimize controls for grid view */
   :deep(.vjs-control-bar) {
     height: 2em;
   }
   
   :deep(.vjs-big-play-button) {
     font-size: 1.5em;
     border-radius: 50%;
     height: 2em;
     width: 2em;
     line-height: 2em;
     margin-left: -1em;
     margin-top: -1em;
   }
   </style>
   ```

### Main View Component

**ChannelMonitor.vue**
```vue
<template>
  <div class="channel-monitor">
    <div class="toolbar">
      <div class="view-controls">
        <button @click="toggleCompactView">
          {{ isCompactView ? 'Standard View' : 'Compact View' }}
        </button>
        <button @click="toggleMuteAll">
          {{ allMuted ? 'Unmute All' : 'Mute All' }}
        </button>
        <select v-model="gridSize" class="grid-size-selector">
          <option value="4">4 Channels</option>
          <option value="9">9 Channels</option>
          <option value="16">16 Channels</option>
          <option value="25">25 Channels</option>
          <option value="36">36 Channels</option>
          <option value="49">49 Channels</option>
          <option value="60">60 Channels</option>
        </select>
      </div>
      <div class="search-filter">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search channels..." 
          class="search-input"
        />
      </div>
    </div>
    
    <LoadingSpinner v-if="loading" />
    <ErrorMessage v-if="error" :message="error" />
    
    <ChannelGrid 
      v-if="!loading && !error"
      :channels="filteredChannels" 
      :is-compact-view="isCompactView"
      @channel-selected="handleChannelSelect"
    />
  </div>
</template>

<script>
import ChannelGrid from '@/components/channel/ChannelGrid.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import ErrorMessage from '@/components/common/ErrorMessage.vue';
import { mapState, mapActions } from 'vuex';

export default {
  components: {
    ChannelGrid,
    LoadingSpinner,
    ErrorMessage
  },
  data() {
    return {
      isCompactView: false,
      allMuted: true,
      searchQuery: '',
      gridSize: '16',
      loading: false,
      error: null
    };
  },
  computed: {
    ...mapState('channels', ['channels']),
    
    filteredChannels() {
      if (!this.searchQuery) {
        return this.channels.slice(0, parseInt(this.gridSize));
      }
      
      const query = this.searchQuery.toLowerCase();
      return this.channels
        .filter(channel => 
          channel.name.toLowerCase().includes(query) || 
          channel.description.toLowerCase().includes(query)
        )
        .slice(0, parseInt(this.gridSize));
    }
  },
  methods: {
    ...mapActions('channels', ['fetchChannels', 'updateChannelStatus']),
    
    toggleCompactView() {
      this.isCompactView = !this.isCompactView;
    },
    
    toggleMuteAll() {
      this.allMuted = !this.allMuted;
      // Implementation to mute/unmute all channels
    },
    
    handleChannelSelect(channelId) {
      // Implementation for channel selection
    },
    
    async loadChannels() {
      this.loading = true;
      this.error = null;
      
      try {
        await this.fetchChannels();
      } catch (err) {
        this.error = 'Failed to load channels. Please try again.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    }
  },
  created() {
    this.loadChannels();
    
    // Set up WebSocket connection for real-time updates
    this.$websocket.subscribe('stream-status', (data) => {
      this.updateChannelStatus(data);
    });
  },
  beforeUnmount() {
    this.$websocket.unsubscribe('stream-status');
  }
};
</script>

<style scoped>
.channel-monitor {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background-color: #2a2a2a;
  border-bottom: 1px solid #3a3a3a;
}

.view-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.view-controls button {
  background-color: #3a3a3a;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 12px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.view-controls button:hover {
  background-color: #4a4a4a;
}

.grid-size-selector {
  background-color: #3a3a3a;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 12px;
  cursor: pointer;
}

.search-input {
  background-color: #3a3a3a;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 12px;
  width: 250px;
}

.search-input::placeholder {
  color: #aaa;
}
</style>
```

### Store Module

**channels.js**
```javascript
import api from '@/services/api';

export default {
  namespaced: true,
  
  state: {
    channels: [],
    selectedChannelId: null,
    loading: false,
    error: null
  },
  
  getters: {
    getChannelById: (state) => (id) => {
      return state.channels.find(channel => channel.id === id);
    },
    
    onlineChannels: (state) => {
      return state.channels.filter(channel => channel.isOnline);
    },
    
    offlineChannels: (state) => {
      return state.channels.filter(channel => !channel.isOnline);
    },
    
    selectedChannel: (state, getters) => {
      return getters.getChannelById(state.selectedChannelId);
    }
  },
  
  mutations: {
    SET_CHANNELS(state, channels) {
      state.channels = channels;
    },
    
    SET_SELECTED_CHANNEL(state, channelId) {
      state.selectedChannelId = channelId;
    },
    
    UPDATE_CHANNEL_STATUS(state, { channelId, status, isOnline }) {
      const channel = state.channels.find(c => c.id === channelId);
      if (channel) {
        channel.status = status;
        channel.isOnline = isOnline;
      }
    },
    
    SET_LOADING(state, loading) {
      state.loading = loading;
    },
    
    SET_ERROR(state, error) {
      state.error = error;
    }
  },
  
  actions: {
    async fetchChannels({ commit }) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      
      try {
        const response = await api.get('/channels');
        commit('SET_CHANNELS', response.data.data);
        return response.data.data;
      } catch (error) {
        commit('SET_ERROR', error.message || 'Failed to fetch channels');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    selectChannel({ commit }, channelId) {
      commit('SET_SELECTED_CHANNEL', channelId);
    },
    
    updateChannelStatus({ commit }, { channelId, status, isOnline }) {
      commit('UPDATE_CHANNEL_STATUS', { channelId, status, isOnline });
    }
  }
};
```

### WebSocket Service

**websocketService.js**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

class WebSocketService {
  constructor() {
    this.echo = null;
    this.connected = false;
    this.callbacks = {};
  }
  
  connect(token) {
    if (this.connected) return;
    
    window.Pusher = Pusher;
    
    this.echo = new Echo({
      broadcaster: 'pusher',
      key: process.env.VUE_APP_WEBSOCKET_KEY,
      wsHost: process.env.VUE_APP_WEBSOCKET_HOST,
      wsPort: process.env.VUE_APP_WEBSOCKET_PORT,
      wssPort: process.env.VUE_APP_WEBSOCKET_PORT,
      forceTLS: false,
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      auth: {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    });
    
    this.connected = true;
  }
  
  subscribe(channel, callback) {
    if (!this.connected) {
      console.error('WebSocket not connected');
      return;
    }
    
    if (!this.callbacks[channel]) {
      this.callbacks[channel] = [];
      
      this.echo.channel(`stream-status`)
        .listen('.StreamStatusChanged', (data) => {
          this.callbacks[channel].forEach(cb => cb(data));
        });
    }
    
    this.callbacks[channel].push(callback);
  }
  
  unsubscribe(channel, callback) {
    if (!this.callbacks[channel]) return;
    
    if (callback) {
      this.callbacks[channel] = this.callbacks[channel].filter(cb => cb !== callback);
    } else {
      delete this.callbacks[channel];
    }
  }
  
  disconnect() {
    if (this.echo) {
      this.echo.disconnect();
      this.echo = null;
    }
    
    this.connected = false;
    this.callbacks = {};
  }
}

export default new WebSocketService();
``` 