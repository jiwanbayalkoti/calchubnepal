<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'qr_code_id',
        'scanned_at',
        'country',
        'device',
        'browser',
        'os',
        'referrer',
        'ip_hash',
        'ip_truncated',
        'session_id',
        'user_agent',
        'city',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
