<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'calculator_id',
        'inputs',
        'outputs',
        'explanation',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'outputs' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }

    public function scopeForCalculator($query, int $calculatorId)
    {
        return $query->where('calculator_id', $calculatorId);
    }
}
