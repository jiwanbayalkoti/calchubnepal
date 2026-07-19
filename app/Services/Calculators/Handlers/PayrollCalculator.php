<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Employee payroll calculator: aggregates basic salary, allowances and
 * overtime pay into a gross salary, then deducts tax and other
 * deductions to arrive at the net take-home pay.
 */
class PayrollCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'payroll_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('basic_salary', 'Basic Salary', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50000]),
            $this->field('allowances', 'Allowances', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('overtime_hours', 'Overtime Hours', 'number', ['unit' => 'hours', 'min' => 0, 'max' => 500, 'step' => 0.5, 'default' => 0, 'required' => false]),
            $this->field('overtime_rate_per_hour', 'Overtime Rate per Hour', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 100000, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('tax_percent', 'Tax Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('other_deductions', 'Other Deductions', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $basicSalary = $this->requireNumeric($inputs, 'basic_salary');
        $allowances = $this->toFloat($inputs, 'allowances', 0);
        $overtimeHours = $this->toFloat($inputs, 'overtime_hours', 0);
        $overtimeRate = $this->toFloat($inputs, 'overtime_rate_per_hour', 0);
        $taxPercent = $this->toFloat($inputs, 'tax_percent', 0);
        $otherDeductions = $this->toFloat($inputs, 'other_deductions', 0);

        $overtimePay = $overtimeHours * $overtimeRate;
        $grossSalary = $basicSalary + $allowances + $overtimePay;
        $taxAmount = $grossSalary * $taxPercent / 100;
        $netSalary = $grossSalary - $taxAmount - $otherDeductions;

        return [
            'results' => [
                'gross_salary' => $this->round($grossSalary),
                'tax_amount' => $this->round($taxAmount),
                'net_salary' => $this->round($netSalary),
            ],
            'breakdown' => [
                'basic_salary' => $this->round($basicSalary),
                'overtime_pay' => $this->round($overtimePay),
                'other_deductions' => $this->round($otherDeductions),
            ],
            'units' => [
                'gross_salary' => 'currency',
                'tax_amount' => 'currency',
                'net_salary' => 'currency',
            ],
        ];
    }
}
