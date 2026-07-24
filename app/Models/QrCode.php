<?php

namespace App\Models;

use App\Enums\Qr\QrStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QrCode extends Model
{
    protected $fillable = [
        'uuid',
        'short_code',
        'user_id',
        'session_id',
        'type',
        'payload',
        'destination_url',
        'input_json',
        'style_json',
        'title',
        'is_saved',
        'is_dynamic',
        'status',
        'password_hash',
        'expires_at',
        'scan_count',
        'last_scanned_at',
        'preview_path',
        'workspace_id',
        'campaign_id',
        'brand_template_id',
    ];

    protected function casts(): array
    {
        return [
            'input_json' => 'array',
            'style_json' => 'array',
            'is_saved' => 'boolean',
            'is_dynamic' => 'boolean',
            'expires_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'scan_count' => 'integer',
            'status' => QrStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(QrWorkspace::class, 'workspace_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }

    public function brandTemplate(): BelongsTo
    {
        return $this->belongsTo(QrBrandTemplate::class, 'brand_template_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function shortUrl(): ?string
    {
        if (! $this->is_dynamic || blank($this->short_code)) {
            return null;
        }

        return url('/q/'.$this->short_code);
    }

    public function isPasswordProtected(): bool
    {
        return filled($this->password_hash);
    }

    public function checkPassword(string $password): bool
    {
        return $this->isPasswordProtected() && Hash::check($password, (string) $this->password_hash);
    }

    public function isExpired(): bool
    {
        if ($this->status === QrStatus::Expired) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPaused(): bool
    {
        return $this->status === QrStatus::Paused;
    }

    public function isRedirectable(): bool
    {
        return $this->is_dynamic
            && $this->status === QrStatus::Active
            && ! $this->isExpired()
            && filled($this->destination_url);
    }

    public function previewUrl(): ?string
    {
        if (! filled($this->preview_path)) {
            return null;
        }

        return asset('storage/'.$this->preview_path);
    }
}
