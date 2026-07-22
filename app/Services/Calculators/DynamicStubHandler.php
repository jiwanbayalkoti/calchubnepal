<?php

namespace App\Services\Calculators;

/**
 * Fallback handler for catalog stub calculators that are listed in the
 * database but do not yet have a dedicated formula implementation.
 */
class DynamicStubHandler extends AbstractCalculatorHandler
{
    public function __construct(protected string $formulaKey)
    {
    }

    public function key(): string
    {
        return $this->formulaKey;
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Primary Amount / Value', 'number', [
                'min' => 0,
                'step' => 0.01,
                'default' => 1000,
                'required' => true,
            ]),
            $this->field('rate', 'Rate / Percent (optional)', 'number', [
                'min' => 0,
                'max' => 100,
                'step' => 0.01,
                'default' => 5,
                'required' => false,
            ]),
            $this->field('notes', 'Notes (optional)', 'string', [
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->toFloat($inputs, 'amount', 0);
        $rate = $this->toFloat($inputs, 'rate', 0);

        return [
            'results' => [
                'status' => 'Coming soon',
                'preview_amount' => $this->round($amount),
                'preview_rate' => $this->round($rate, 2),
            ],
            'breakdown' => [
                'message' => 'This calculator is published in the catalog. Full formula engine will be enabled soon. Values above are a preview echo of your inputs.',
            ],
            'units' => [
                'preview_amount' => '',
                'preview_rate' => '%',
            ],
        ];
    }
}
