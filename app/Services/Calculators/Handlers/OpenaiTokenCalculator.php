<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * OpenAI Token Cost Calculator
 */
class OpenaiTokenCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'openai_token_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('input_tokens', 'Input Tokens', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 1000]),
            $this->field('output_tokens', 'Output Tokens', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 500]),
            $this->field('input_price', 'Input Price / 1M tokens', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.15, 'unit' => 'USD']),
            $this->field('output_price', 'Output Price / 1M tokens', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.6, 'unit' => 'USD']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $in = $this->requireNumeric($inputs, 'input_tokens');
        $out = $this->requireNumeric($inputs, 'output_tokens');
        $inCost = ($in / 1_000_000) * $this->requireNumeric($inputs, 'input_price');
        $outCost = ($out / 1_000_000) * $this->requireNumeric($inputs, 'output_price');
        return [
            'results' => [
                'input_cost' => $this->round($inCost, 6),
                'output_cost' => $this->round($outCost, 6),
                'total_cost' => $this->round($inCost + $outCost, 6),
                'total_tokens' => (int) ($in + $out),
            ],
            'breakdown' => ['note' => 'Enter your model’s published per-1M rates'],
            'units' => ['input_cost' => 'USD', 'output_cost' => 'USD', 'total_cost' => 'USD', 'total_tokens' => 'tokens'],
        ];
    }
}
