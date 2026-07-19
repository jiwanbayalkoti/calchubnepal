<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Nepal driving-license fee estimator.
 * Base fees historically ~NPR 1,500 (A/K) and NPR 2,000 (B); several
 * provinces have doubled fees under FY 2083/84 provincial finance bills.
 * Late fines escalate after the 90-day grace period.
 */
class DrivingLicenseFeeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'driving_license_fee_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('category', 'Category', 'select', [
                'options' => [
                    'a' => 'A / K (Motorcycle / scooter)',
                    'b' => 'B (Car / jeep / van)',
                    'both' => 'A + B (combined estimate)',
                    'heavy' => 'Heavy / commercial (indicative)',
                ],
                'default' => 'b',
            ]),
            $this->field('type', 'Application Type', 'select', [
                'options' => [
                    'new' => 'New license',
                    'renewal' => 'Renewal',
                ],
                'default' => 'renewal',
            ]),
            $this->field('fee_schedule', 'Fee Schedule', 'select', [
                'options' => [
                    'standard' => 'Prior / standard schedule (1,500 / 2,000)',
                    'doubled' => 'Doubled provincial schedule (3,000 / 4,000)',
                ],
                'default' => 'standard',
            ]),
            $this->field('late_years', 'Years Late After Grace (0–5)', 'number', [
                'min' => 0,
                'max' => 5,
                'step' => 1,
                'default' => 0,
                'required' => false,
            ]),
            $this->field('include_medical', 'Include medical / color-test (NPR 15)', 'boolean', [
                'default' => true,
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $category = $this->toString($inputs, 'category', 'b');
        $doubled = $this->toString($inputs, 'fee_schedule', 'standard') === 'doubled';
        $lateYears = (int) $this->toFloat($inputs, 'late_years', 0);
        $includeMedical = $this->toBool($inputs, 'include_medical', true);

        $base = match ($category) {
            'a' => $doubled ? 3000 : 1500,
            'both' => $doubled ? 7000 : 3500,
            'heavy' => $doubled ? 5000 : 2500,
            default => $doubled ? 4000 : 2000,
        };

        // Renewal uses same category fee under current DOTM practice
        $renewalFee = $base;
        $lateMultiplier = match (true) {
            $lateYears <= 0 => 0,
            $lateYears === 1 => 1,
            $lateYears === 2 => 2,
            $lateYears === 3 => 3,
            $lateYears === 4 => 4,
            default => 5,
        };
        $lateFine = $renewalFee * $lateMultiplier;
        $medical = $includeMedical ? 15 : 0;
        $total = $renewalFee + $lateFine + $medical;

        return [
            'results' => [
                'base_fee_npr' => $renewalFee,
                'late_fine_npr' => $lateFine,
                'medical_fee_npr' => $medical,
                'estimated_total_npr' => $total,
            ],
            'breakdown' => [
                'application_type' => $this->toString($inputs, 'type', 'renewal'),
                'fee_schedule' => $doubled ? 'Doubled provincial (indicative)' : 'Standard / prior schedule',
                'late_fine_rule' => 'After 90-day grace: ~100% of renewal fee per year late (capped illustrative 500% at 5 years)',
                'note' => 'Fees are provincial — confirm with your Transport Management Office. Licenses expired >5 years often cannot be renewed.',
            ],
            'units' => [
                'base_fee_npr' => 'NPR',
                'late_fine_npr' => 'NPR',
                'medical_fee_npr' => 'NPR',
                'estimated_total_npr' => 'NPR',
            ],
        ];
    }
}
