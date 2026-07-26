<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreathHoldResult extends Model
{
    protected $fillable = [
        'user_id',
        'claim_token',
        'certificate_code',
        'duration_ms',
        'duration_seconds',
        'band',
        'certificate_issued_at',
        'ip_hash',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'duration_seconds' => 'float',
            'certificate_issued_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCertificate(): bool
    {
        return filled($this->certificate_code) && $this->certificate_issued_at !== null;
    }

    public function formattedDuration(): string
    {
        $total = max(0, (float) $this->duration_seconds);
        $mins = (int) floor($total / 60);
        $secs = $total - ($mins * 60);

        if ($mins > 0) {
            return sprintf('%02d:%05.2f', $mins, $secs);
        }

        return number_format($secs, 2).'s';
    }

    public function bandLabel(): string
    {
        return match ($this->band) {
            'poor' => 'Poor',
            'medium' => 'Medium',
            'healthy' => 'Healthy',
            default => ucfirst((string) $this->band),
        };
    }

    public function bandRangeLabel(): string
    {
        return match ($this->band) {
            'poor' => 'Under 20 seconds',
            'medium' => '20–40 seconds',
            'healthy' => '40+ seconds',
            default => 'Custom range',
        };
    }

    public function funnyTitle(): string
    {
        return match ($this->band) {
            'poor' => 'Amateur Air Pauser',
            'medium' => 'Semi-Pro Breath Boss',
            'healthy' => 'Legendary Lung Legend',
            default => 'Certified Breath Holder',
        };
    }

    public function funnySubtitle(): string
    {
        return match ($this->band) {
            'poor' => 'Survived the urge to gasp… briefly. Respect.',
            'medium' => 'Held it long enough to impress a goldfish.',
            'healthy' => 'The air got jealous and asked for a rematch.',
            default => 'Successfully paused the inhale.',
        };
    }

    public function funnyMotto(): string
    {
        return match ($this->band) {
            'poor' => '"I breathed… and I\'m proud."',
            'medium' => '"Inhale courage. Exhale later."',
            'healthy' => '"Oxygen who? Never heard of them."',
            default => '"Hold on — literally."',
        };
    }

    public function funnyStamp(): string
    {
        return match ($this->band) {
            'poor' => 'WARM-UP MODE',
            'medium' => 'SOLID HOLD',
            'healthy' => 'AIR BOSS',
            default => 'CERTIFIED',
        };
    }

    /**
     * @return array{primary:string,accent:string,soft:string,badge:string}
     */
    public function certificatePalette(): array
    {
        return match ($this->band) {
            'poor' => [
                'primary' => '#B45309',
                'accent' => '#F59E0B',
                'soft' => '#FFFBEB',
                'badge' => '#FEF3C7',
            ],
            'medium' => [
                'primary' => '#1D4ED8',
                'accent' => '#38BDF8',
                'soft' => '#EFF6FF',
                'badge' => '#DBEAFE',
            ],
            'healthy' => [
                'primary' => '#047857',
                'accent' => '#34D399',
                'soft' => '#ECFDF5',
                'badge' => '#D1FAE5',
            ],
            default => [
                'primary' => '#0B6E4F',
                'accent' => '#10B981',
                'soft' => '#F7F9F8',
                'badge' => '#E8F5F0',
            ],
        };
    }
}
