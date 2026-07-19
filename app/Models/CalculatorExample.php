<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorExample extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'calculator_id',
        'title',
        'inputs',
        'outputs',
        'explanation',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'outputs' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }
}
