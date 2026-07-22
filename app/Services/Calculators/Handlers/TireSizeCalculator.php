<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Tire Size Calculator — TRA / ETRTO P-metric sizing.
 * Sidewall, overall diameter, revolutions per mile, and optional
 * speedometer error vs a reference size.
 */
class TireSizeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tire_size_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('width', 'Tire Width', 'number', ['min' => 100, 'max' => 500, 'step' => 1, 'default' => 225, 'unit' => 'mm']),
            $this->field('aspect', 'Aspect Ratio', 'number', ['min' => 20, 'max' => 95, 'step' => 1, 'default' => 45, 'unit' => '%']),
            $this->field('rim', 'Rim Diameter', 'number', ['min' => 10, 'max' => 30, 'step' => 0.5, 'default' => 17, 'unit' => 'in']),
            $this->field('ref_width', 'Reference Width (optional)', 'number', ['min' => 0, 'max' => 500, 'step' => 1, 'default' => 225, 'unit' => 'mm', 'required' => false]),
            $this->field('ref_aspect', 'Reference Aspect (optional)', 'number', ['min' => 0, 'max' => 95, 'step' => 1, 'default' => 45, 'unit' => '%', 'required' => false]),
            $this->field('ref_rim', 'Reference Rim (optional)', 'number', ['min' => 0, 'max' => 30, 'step' => 0.5, 'default' => 17, 'unit' => 'in', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $width = $this->requireNumeric($inputs, 'width');
        $aspect = $this->requireNumeric($inputs, 'aspect');
        $rim = $this->requireNumeric($inputs, 'rim');

        $geo = $this->geometry($width, $aspect, $rim);

        $results = [
            'sidewall_mm' => $this->round($geo['sidewall_mm'], 1),
            'sidewall_in' => $this->round($geo['sidewall_mm'] / 25.4, 2),
            'overall_diameter_mm' => $this->round($geo['diameter_mm'], 1),
            'overall_diameter_in' => $this->round($geo['diameter_in'], 2),
            'circumference_in' => $this->round($geo['circumference_in'], 2),
            'revolutions_per_mile' => $this->round($geo['revs_per_mile'], 1),
        ];

        $units = [
            'sidewall_mm' => 'mm',
            'sidewall_in' => 'in',
            'overall_diameter_mm' => 'mm',
            'overall_diameter_in' => 'in',
            'circumference_in' => 'in',
            'revolutions_per_mile' => 'rev/mi',
        ];

        $breakdown = [
            'size_code' => sprintf('%g/%gR%g', $width, $aspect, $rim),
            'formula' => 'diameter = (rim × 25.4) + 2 × (width × aspect/100); revs/mi = 63360 ÷ (π × diameter_in)',
        ];

        $refWidth = $this->toFloat($inputs, 'ref_width', 0);
        $refAspect = $this->toFloat($inputs, 'ref_aspect', 0);
        $refRim = $this->toFloat($inputs, 'ref_rim', 0);

        if ($refWidth > 0 && $refAspect > 0 && $refRim > 0) {
            $ref = $this->geometry($refWidth, $refAspect, $refRim);
            $diameterDelta = $geo['diameter_in'] - $ref['diameter_in'];
            $speedoErrorPct = $this->safeDivide($diameterDelta, $ref['diameter_in']) * 100;
            // Indicated 60 mph → actual speed scales with new/old diameter.
            $actualAt60 = 60 * $this->safeDivide($geo['diameter_in'], $ref['diameter_in'], 1);

            $results['diameter_difference_in'] = $this->round($diameterDelta, 3);
            $results['diameter_difference_pct'] = $this->round($this->safeDivide($diameterDelta, $ref['diameter_in']) * 100, 2);
            $results['speedometer_error_pct'] = $this->round($speedoErrorPct, 2);
            $results['actual_speed_at_indicated_60'] = $this->round($actualAt60, 1);

            $units['diameter_difference_in'] = 'in';
            $units['diameter_difference_pct'] = '%';
            $units['speedometer_error_pct'] = '%';
            $units['actual_speed_at_indicated_60'] = 'mph';

            $breakdown['reference_size'] = sprintf('%g/%gR%g', $refWidth, $refAspect, $refRim);
            $breakdown['reference_diameter_in'] = $this->round($ref['diameter_in'], 2);
            $breakdown['note'] = $speedoErrorPct > 0
                ? 'Larger tire: speedometer reads low (you are faster than indicated).'
                : ($speedoErrorPct < 0
                    ? 'Smaller tire: speedometer reads high (you are slower than indicated).'
                    : 'Same overall diameter — no speedometer error.');
        }

        return [
            'results' => $results,
            'breakdown' => $breakdown,
            'units' => $units,
        ];
    }

    /**
     * @return array{sidewall_mm: float, diameter_mm: float, diameter_in: float, circumference_in: float, revs_per_mile: float}
     */
    protected function geometry(float $widthMm, float $aspectPct, float $rimIn): array
    {
        $sidewallMm = $widthMm * ($aspectPct / 100);
        $diameterMm = ($rimIn * 25.4) + (2 * $sidewallMm);
        $diameterIn = $diameterMm / 25.4;
        $circumferenceIn = pi() * $diameterIn;
        $revsPerMile = $this->safeDivide(63360, $circumferenceIn);

        return [
            'sidewall_mm' => $sidewallMm,
            'diameter_mm' => $diameterMm,
            'diameter_in' => $diameterIn,
            'circumference_in' => $circumferenceIn,
            'revs_per_mile' => $revsPerMile,
        ];
    }
}
