<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QrBulkJob extends Model
{
    protected $table = 'qr_bulk_jobs';

    protected $fillable = [
        'uuid',
        'user_id',
        'workspace_id',
        'campaign_id',
        'brand_template_id',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'input_path',
        'output_zip_path',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'error_log' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'failed_rows' => 'integer',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }

    public function brandTemplate(): BelongsTo
    {
        return $this->belongsTo(QrBrandTemplate::class, 'brand_template_id');
    }

    public function zipUrl(): ?string
    {
        return $this->output_zip_path ? asset('storage/'.$this->output_zip_path) : null;
    }

    public function isReady(): bool
    {
        return $this->status === 'completed' && filled($this->output_zip_path);
    }
}
