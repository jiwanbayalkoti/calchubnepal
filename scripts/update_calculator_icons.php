<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Support\CalculatorIconMap;

$catUpdated = 0;
foreach (CalculatorCategory::query()->get() as $category) {
    $icon = CalculatorIconMap::forCategory($category->slug);
    if ($category->icon !== $icon) {
        $category->update(['icon' => $icon]);
        $catUpdated++;
    }
}

$calcUpdated = 0;
$missing = [];
$map = CalculatorIconMap::calculators();

foreach (Calculator::query()->get() as $calculator) {
    $key = $calculator->formula_key ?: str_replace('-', '_', $calculator->slug);
    $icon = CalculatorIconMap::forCalculator($key);

    if (! isset($map[$key])) {
        $missing[] = $key;
    }

    if ($calculator->icon !== $icon) {
        $calculator->update(['icon' => $icon]);
        $calcUpdated++;
    }
}

echo "Categories updated: {$catUpdated}\n";
echo "Calculators updated: {$calcUpdated}\n";
echo 'Mapped keys: '.count($map)."\n";

if ($missing !== []) {
    echo 'Unmapped (using bi-calculator): '.count($missing)."\n";
    foreach ($missing as $key) {
        echo " - {$key}\n";
    }
} else {
    echo "All calculator keys have explicit icons.\n";
}
