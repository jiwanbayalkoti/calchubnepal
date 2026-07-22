<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Commute Cost + Time Calculator
 * Vehicle (fuel + wear + parking) plus lost-time cost at hourly wage; remote-work savings.
 */
class CommuteCostTimeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'commute_cost_time_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('one_way_miles', 'One-Way Distance', 'number', ['min' => 0.1, 'max' => 200, 'step' => 0.1, 'default' => 15, 'unit' => 'mi']),
            $this->field('one_way_minutes', 'One-Way Time', 'number', ['min' => 1, 'max' => 300, 'step' => 1, 'default' => 35, 'unit' => 'min']),
            $this->field('days_per_week', 'Commute Days / Week', 'number', ['min' => 1, 'max' => 7, 'step' => 1, 'default' => 5, 'unit' => 'days']),
            $this->field('weeks_per_year', 'Weeks / Year', 'number', ['min' => 1, 'max' => 52, 'step' => 1, 'default' => 48, 'unit' => 'weeks']),
            $this->field('mpg', 'Fuel Economy', 'number', ['min' => 1, 'max' => 150, 'step' => 0.1, 'default' => 28, 'unit' => 'mpg']),
            $this->field('fuel_price', 'Fuel Price / Gallon', 'number', ['min' => 0, 'max' => 20, 'step' => 0.01, 'default' => 3.50, 'unit' => 'currency']),
            $this->field('wear_per_mile', 'Wear & Tear / Mile', 'number', ['min' => 0, 'max' => 2, 'step' => 0.01, 'default' => 0.10, 'unit' => 'currency/mi', 'required' => false]),
            $this->field('parking_per_day', 'Parking / Day', 'number', ['min' => 0, 'max' => 100, 'step' => 0.5, 'default' => 10, 'unit' => 'currency', 'required' => false]),
            $this->field('hourly_wage', 'Your Hourly Wage (or value of time)', 'number', ['min' => 0, 'max' => 1000, 'step' => 0.5, 'default' => 30, 'unit' => 'currency/hr']),
            $this->field('remote_days_per_week', 'Remote Days / Week (for savings)', 'number', ['min' => 0, 'max' => 7, 'step' => 1, 'default' => 2, 'unit' => 'days', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $oneWayMiles = $this->requireNumeric($inputs, 'one_way_miles');
        $oneWayMinutes = $this->requireNumeric($inputs, 'one_way_minutes');
        $daysWeek = max(1, $this->requireNumeric($inputs, 'days_per_week'));
        $weeks = max(1, $this->requireNumeric($inputs, 'weeks_per_year'));
        $mpg = $this->requireNumeric($inputs, 'mpg');
        $fuelPrice = $this->requireNumeric($inputs, 'fuel_price');
        $wear = $this->toFloat($inputs, 'wear_per_mile', 0.10);
        $parking = $this->toFloat($inputs, 'parking_per_day', 0);
        $wage = $this->requireNumeric($inputs, 'hourly_wage');
        $remoteDays = min($daysWeek, max(0, $this->toFloat($inputs, 'remote_days_per_week', 0)));

        $dailyMiles = $oneWayMiles * 2;
        $dailyFuel = $this->safeDivide($dailyMiles, $mpg) * $fuelPrice;
        $dailyWear = $dailyMiles * $wear;
        $dailyVehicle = $dailyFuel + $dailyWear + $parking;
        $dailyHours = ($oneWayMinutes * 2) / 60;
        $dailyTimeCost = $dailyHours * $wage;
        $dailyFull = $dailyVehicle + $dailyTimeCost;

        $annualDays = $daysWeek * $weeks;
        $annualVehicle = $dailyVehicle * $annualDays;
        $annualTime = $dailyTimeCost * $annualDays;
        $annualFull = $dailyFull * $annualDays;
        $annualFuelOnly = $dailyFuel * $annualDays;

        $remoteFraction = $this->safeDivide($remoteDays, $daysWeek);
        $remoteAnnualSavings = $annualFull * $remoteFraction;

        return [
            'results' => [
                'daily_vehicle_cost' => $this->round($dailyVehicle),
                'daily_time_cost' => $this->round($dailyTimeCost),
                'daily_full_cost' => $this->round($dailyFull),
                'annual_vehicle_cost' => $this->round($annualVehicle),
                'annual_time_cost' => $this->round($annualTime),
                'annual_full_cost' => $this->round($annualFull),
                'annual_fuel_only' => $this->round($annualFuelOnly),
                'remote_work_annual_savings' => $this->round($remoteAnnualSavings),
                'hours_per_year_commuting' => $this->round($dailyHours * $annualDays, 1),
            ],
            'breakdown' => [
                'daily_miles' => $this->round($dailyMiles, 1),
                'daily_fuel' => $this->round($dailyFuel),
                'daily_wear' => $this->round($dailyWear),
                'daily_parking' => $this->round($parking),
                'commute_days_per_year' => $this->round($annualDays, 0),
                'remote_days_per_week' => $remoteDays,
                'formula' => 'Full cost = fuel + wear + parking + (round-trip hours × wage); remote savings = full × remote_days/commute_days',
            ],
            'units' => [
                'daily_vehicle_cost' => 'currency',
                'daily_time_cost' => 'currency',
                'daily_full_cost' => 'currency',
                'annual_vehicle_cost' => 'currency',
                'annual_time_cost' => 'currency',
                'annual_full_cost' => 'currency',
                'annual_fuel_only' => 'currency',
                'remote_work_annual_savings' => 'currency',
                'hours_per_year_commuting' => 'hours',
            ],
        ];
    }
}
