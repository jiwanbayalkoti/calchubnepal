<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageView extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'calculable_type',
        'calculable_id',
        'path',
        'referrer',
        'country',
        'device',
        'ip_hash',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function calculable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePath($query, string $path)
    {
        return $query->where('path', $path);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
