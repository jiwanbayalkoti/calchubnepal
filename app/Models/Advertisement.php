<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'advertiser_id',
        'name',
        'slug',
        'position',
        'banner_size',
        'ad_type',
        'content',
        'image',
        'link_url',
        'adsense_code',
        'is_active',
        'status',
        'start_at',
        'end_at',
        'sort_order',
        'created_by',
        'updated_by',
        'assigned_by',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'assigned_at' => 'datetime',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function impressionLogs(): HasMany
    {
        return $this->hasMany(AdvertisementImpression::class);
    }

    public function clickLogs(): HasMany
    {
        return $this->hasMany(AdvertisementClick::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AdvertisementAssignment::class);
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
        if (! $this->is_active || $this->status === self::STATUS_PAUSED || $this->status === self::STATUS_EXPIRED) {
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

    public function runningDays(): int
    {
        if (! $this->start_at) {
            return 0;
        }

        $end = now()->min($this->end_at ?? now());

        return max(0, (int) $this->start_at->diffInDays($end));
    }

    public function remainingDays(): ?int
    {
        if (! $this->end_at) {
            return null;
        }

        if ($this->end_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->end_at);
    }

    public function ctr(): float
    {
        if ($this->impressions <= 0) {
            return 0.0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
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
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeForAdvertiser($query, int $advertiserId)
    {
        return $query->where('advertiser_id', $advertiserId);
    }

    public function syncRuntimeStatus(): void
    {
        if ($this->end_at && $this->end_at->isPast() && $this->status !== self::STATUS_EXPIRED) {
            $this->forceFill([
                'status' => self::STATUS_EXPIRED,
                'is_active' => false,
            ])->save();
        }
    }
}
