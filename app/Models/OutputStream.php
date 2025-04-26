<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutputStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'protocol',
        'url',
        'multiview_layout_id',
        'status',
        'ffmpeg_options',
        'metadata',
        'error_log',
    ];

    protected $casts = [
        'ffmpeg_options' => 'array',
        'metadata' => 'array',
        'error_log' => 'array',
    ];

    /**
     * Get the multiview layout that this output stream uses.
     */
    public function multiviewLayout(): BelongsTo
    {
        return $this->belongsTo(MultiviewLayout::class);
    }

    /**
     * Get the protocols available for output streams.
     */
    public static function getProtocols(): array
    {
        return [
            'direct' => 'Direct (No conversion)',
            'hls' => 'HLS',
            'http' => 'HTTP',
            'dash' => 'DASH',
            'rtsp' => 'RTSP',
            'udp' => 'UDP',
        ];
    }

    /**
     * Get the statuses available for output streams.
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
     * Get the processed URL with placeholders replaced.
     */
    public function getProcessedUrlAttribute(): string
    {
        if (!$this->url) {
            return '';
        }
        
        $url = $this->url;
        
        // Replace {storage_path} with the actual storage path
        $url = str_replace('{storage_path}', storage_path('app/public'), $url);
        
        // Replace {id} with the stream ID
        $url = str_replace('{id}', $this->id, $url);
        
        return $url;
    }
    
    /**
     * Ensure directory exists for storage path URLs
     */
    public function ensureStorageDirectoryExists(): void
    {
        if (strpos($this->url, '{storage_path}') !== false) {
            $processedUrl = $this->processed_url;
            $directory = dirname($processedUrl);
            
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }
}
