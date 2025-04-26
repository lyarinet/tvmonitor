<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayoutPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'multiview_layout_id',
        'input_stream_id',
        'position_x',
        'position_y',
        'width',
        'height',
        'z_index',
        'show_label',
        'label_position',
        'overlay_options',
    ];

    protected $casts = [
        'overlay_options' => 'array',
        'show_label' => 'boolean',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the layout that this position belongs to.
     */
    public function multiviewLayout(): BelongsTo
    {
        return $this->belongsTo(MultiviewLayout::class);
    }

    /**
     * Get the input stream that this position displays.
     */
    public function inputStream(): BelongsTo
    {
        return $this->belongsTo(InputStream::class);
    }

    /**
     * Get the label positions available.
     */
    public static function getLabelPositions(): array
    {
        return [
            'top' => 'Top',
            'bottom' => 'Bottom',
            'left' => 'Left',
            'right' => 'Right',
        ];
    }
}
