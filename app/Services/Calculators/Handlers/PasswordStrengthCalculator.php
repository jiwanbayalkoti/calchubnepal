<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Password Strength Checker
 */
class PasswordStrengthCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'password_strength_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('password', 'Password', 'text', ['default' => 'MyP@ssw0rd']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $password = $this->toString($inputs, 'password', '');
        $score = 0;
        $len = strlen($password);
        if ($len >= 8) { $score += 25; }
        if ($len >= 12) { $score += 15; }
        if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) { $score += 20; }
        if (preg_match('/\d/', $password)) { $score += 20; }
        if (preg_match('/[^A-Za-z0-9]/', $password)) { $score += 20; }
        $label = match (true) {
            $score >= 80 => 'Strong',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            default => 'Weak',
        };
        return [
            'results' => ['strength_score' => $score, 'strength_label' => $label, 'length' => $len],
            'breakdown' => ['note' => 'Heuristic score only — not a security audit'],
            'units' => ['strength_score' => '0-100', 'strength_label' => 'rating', 'length' => 'chars'],
        ];
    }
}
