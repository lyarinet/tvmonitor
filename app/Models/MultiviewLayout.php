<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MultiviewLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rows',
        'columns',
        'width',
        'height',
        'background_color',
        'status',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'width' => 'integer',
        'height' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the layout positions for this multiview layout.
     */
    public function layoutPositions(): HasMany
    {
        return $this->hasMany(LayoutPosition::class);
    }

    /**
     * Get the output streams for this multiview layout.
     */
    public function outputStreams(): HasMany
    {
        return $this->hasMany(OutputStream::class, 'multiview_layout_id');
    }

    /**
     * Get the statuses available for multiview layouts.
     */
    public static function getStatuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Generate a grid layout based on rows and columns.
     * This creates evenly spaced layout positions.
     */
    public function generateGridLayout(): void
    {
        // Clear existing positions
        $this->layoutPositions()->delete();

        $cellWidth = floor($this->width / $this->columns);
        $cellHeight = floor($this->height / $this->rows);

        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->columns; $col++) {
                $this->layoutPositions()->create([
                    'position_x' => $col * $cellWidth,
                    'position_y' => $row * $cellHeight,
                    'width' => $cellWidth,
                    'height' => $cellHeight,
                    'z_index' => 0,
                    'show_label' => true,
                    'label_position' => 'bottom',
                ]);
            }
        }
    }
}
