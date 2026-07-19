<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'key_prefix',
        'last_used_at',
        'rate_limit_per_minute',
        'is_active',
        'expires_at',
    ];

    protected $hidden = [
        'key',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'rate_limit_per_minute' => 'integer',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new plain-text API key and its hashed representation.
     *
     * @return array{plainText: string, prefix: string, hashed: string}
     */
    public static function generateKeyPair(): array
    {
        $plainText = Str::random(40);
        $prefix = substr($plainText, 0, 8);

        return [
            'plainText' => $plainText,
            'prefix' => $prefix,
            'hashed' => hash('sha256', $plainText),
        ];
    }

    public static function findByPlainTextKey(string $plainTextKey): ?self
    {
        return static::query()
            ->where('key', hash('sha256', $plainTextKey))
            ->where('is_active', true)
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
