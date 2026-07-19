<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'ad_type',
        'content',
        'image',
        'link_url',
        'adsense_code',
        'is_active',
        'start_at',
        'end_at',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function hasLocalImage(): bool
    {
        return filled($this->image)
            && ! str_starts_with($this->image, 'http://')
            && ! str_starts_with($this->image, 'https://');
    }

    public function isCurrentlyRunning(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_at && $this->start_at->isAfter($now)) {
            return false;
        }

        if ($this->end_at && $this->end_at->isBefore($now)) {
            return false;
        }

        return true;
    }

    public function recordImpression(): void
    {
        $this->increment('impressions');
    }

    public function recordClick(): void
    {
        $this->increment('clicks');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPosition($query, string $position)
    {
        return $query->where('position', $position);
    }
}
