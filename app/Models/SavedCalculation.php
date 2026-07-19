<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedCalculation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'calculator_id',
        'title',
        'inputs',
        'outputs',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'outputs' => 'array',
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
