<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Time Zone Offset Converter
 */
class TimeZoneConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'time_zone_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('time', 'Local Time', 'time', ['default' => '12:00']),
            $this->field('from_offset', 'From UTC Offset (hours)', 'number', ['min' => -12, 'max' => 14, 'step' => 0.25, 'default' => 5.75]),
            $this->field('to_offset', 'To UTC Offset (hours)', 'number', ['min' => -12, 'max' => 14, 'step' => 0.25, 'default' => 0]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $time = $this->toString($inputs, 'time', '12:00');
        [$h, $m] = array_map('intval', explode(':', $time));
        $minutes = $h * 60 + $m;
        $from = $this->requireNumeric($inputs, 'from_offset') * 60;
        $to = $this->requireNumeric($inputs, 'to_offset') * 60;
        $utc = $minutes - $from;
        $target = $utc + $to;
        while ($target < 0) { $target += 1440; }
        while ($target >= 1440) { $target -= 1440; }
        $out = sprintf('%02d:%02d', intdiv((int) $target, 60), ((int) $target) % 60);
        return [
            'results' => ['converted_time' => $out],
            'breakdown' => ['note' => 'Offset-based conversion (no DST rules)'],
            'units' => ['converted_time' => 'HH:MM'],
        ];
    }
}
