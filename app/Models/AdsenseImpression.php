<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdsenseImpression extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'position',
        'ad_slot',
        'source',
        'advertisement_id',
        'ip_address',
        'device',
        'browser',
        'country',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}
