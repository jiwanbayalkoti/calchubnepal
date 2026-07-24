<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QrCampaign extends Model
{
    protected $table = 'qr_campaigns';

    protected $fillable = [
        'uuid',
        'user_id',
        'workspace_id',
        'name',
        'slug',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (blank($model->slug)) {
                $model->slug = Str::slug($model->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(QrWorkspace::class, 'workspace_id');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'campaign_id');
    }

    public function appendUtm(string $url): string
    {
        $parts = array_filter([
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign ?: $this->slug,
        ]);
        if ($parts === []) {
            return $url;
        }
        $sep = str_contains($url, '?') ? '&' : '?';

        return $url.$sep.http_build_query($parts);
    }
}
