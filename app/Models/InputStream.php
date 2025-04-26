<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'protocol',
        'url',
        'username',
        'password',
        'status',
        'metadata',
        'error_log',
        'local_address',
        'program_id',
        'ignore_unknown',
        'map_disable_data',
        'map_disable_subtitles',
        'additional_options',
    ];

    protected $casts = [
        'metadata' => 'array',
        'error_log' => 'array',
        'ignore_unknown' => 'boolean',
        'map_disable_data' => 'boolean',
        'map_disable_subtitles' => 'boolean',
        'additional_options' => 'array',
    ];

    /**
     * Get the layout positions for this input stream.
     */
    public function layoutPositions(): HasMany
    {
        return $this->hasMany(LayoutPosition::class);
    }

    /**
     * Get the protocols available for input streams.
     */
    public static function getProtocols(): array
    {
        return [
            'rtmp' => 'RTMP',
            'hls' => 'HLS',
            'http' => 'HTTP',
            'dash' => 'DASH',
            'rtsp' => 'RTSP',
            'udp' => 'UDP',
        ];
    }

    /**
     * Get the statuses available for input streams.
     */
    public static function getStatuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'error' => 'Error',
        ];
    }
    
    /**
     * Get the FFmpeg input options for this stream.
     */
    public function getFFmpegInputOptions(): string
    {
        $options = '';
        
        if ($this->protocol === 'udp') {
            // Add program mapping if specified
            if (!empty($this->program_id)) {
                $options .= " -map 0:p:{$this->program_id}";
            }
            
            // Add ignore_unknown option if enabled
            if ($this->ignore_unknown) {
                $options .= ' -ignore_unknown';
            }
            
            // Add map -d option if enabled (disable data streams)
            if ($this->map_disable_data) {
                $options .= ' -map -d';
            }
            
            // Add map -s option if enabled (disable subtitle streams)
            if ($this->map_disable_subtitles) {
                $options .= ' -map -s';
            }
            
            // Add any additional options
            if (!empty($this->additional_options) && is_array($this->additional_options)) {
                foreach ($this->additional_options as $key => $value) {
                    if (!empty($key)) {
                        $options .= " {$key}";
                        if (!empty($value)) {
                            $options .= " {$value}";
                        }
                    }
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Process the URL to include any additional parameters.
     */
    public function getProcessedUrlAttribute(): string
    {
        $url = $this->url;
        
        if ($this->protocol === 'udp' && !empty($this->local_address) && !str_contains($url, 'localaddr=')) {
            // Add localaddr parameter if not already in the URL
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= "{$separator}localaddr={$this->local_address}";
        }
        
        return $url;
    }
}
