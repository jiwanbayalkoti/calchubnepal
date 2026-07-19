<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Calculates exact age (years, months, days) between a birth date and a
 * reference date (defaults to today), plus total elapsed units and a
 * countdown to the next birthday.
 */
class AgeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'age_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('birth_date', 'Date of Birth', 'date', []),
            $this->field('as_of_date', 'As of Date', 'date', ['required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $birthDateRaw = $this->toString($inputs, 'birth_date');

        if ($birthDateRaw === '') {
            throw new InvalidArgumentException('The field [birth_date] is required.');
        }

        $birthDate = Carbon::parse($birthDateRaw)->startOfDay();
        $asOfRaw = $this->toString($inputs, 'as_of_date');
        $asOfDate = $asOfRaw !== '' ? Carbon::parse($asOfRaw)->startOfDay() : Carbon::today();

        if ($birthDate->greaterThan($asOfDate)) {
            throw new InvalidArgumentException('Birth date cannot be after the reference date.');
        }

        $diff = $birthDate->diff($asOfDate);
        $totalDays = $birthDate->diffInDays($asOfDate);
        $totalMonths = $birthDate->diffInMonths($asOfDate);
        $totalWeeks = intdiv($totalDays, 7);

        $nextBirthday = $birthDate->copy()->year($asOfDate->year);
        if ($nextBirthday->lessThan($asOfDate)) {
            $nextBirthday->addYear();
        }
        $daysToNextBirthday = $asOfDate->diffInDays($nextBirthday);

        return [
            'results' => [
                'years' => $diff->y,
                'months' => $diff->m,
                'days' => $diff->d,
                'days_to_next_birthday' => $daysToNextBirthday,
            ],
            'breakdown' => [
                'total_days' => $totalDays,
                'total_months' => $totalMonths,
                'total_weeks' => $totalWeeks,
            ],
            'units' => [
                'years' => 'years',
                'months' => 'months',
                'days' => 'days',
                'days_to_next_birthday' => 'days',
            ],
        ];
    }
}
