<?php

/**
 * One-shot generator: creates missing calculator handlers + category map snippet.
 * Run: php scripts/generate_missing_calculators.php
 */

require_once __DIR__.'/catalog_helpers.php';

$handlersDir = dirname(__DIR__).'/app/Services/Calculators/Handlers';
$mapPath = dirname(__DIR__).'/storage/app/generated_category_map.php';

$catalog = array_merge(
    require __DIR__.'/calculator_catalog.php',
    require __DIR__.'/calculator_catalog_more.php',
    require __DIR__.'/calculator_catalog_part3.php',
    require __DIR__.'/calculator_catalog_part4.php',
    require __DIR__.'/calculator_catalog_part5.php',
);

$created = 0;
$skipped = 0;
$map = [];

foreach ($catalog as $item) {
    $class = $item['class'];
    $key = $item['key'];
    $file = $handlersDir.'/'.$class.'.php';
    $map[$key] = $item['category'];

    if (file_exists($file)) {
        $skipped++;
        continue;
    }

    $code = ($item['type'] ?? null) === 'converter'
        ? buildConverterHandler($item)
        : buildHandler($item);

    file_put_contents($file, $code);
    $created++;
}

ksort($map);
file_put_contents($mapPath, "<?php\n\nreturn ".var_export($map, true).";\n");

echo "Created: {$created}, skipped existing: {$skipped}, total catalog: ".count($catalog)."\n";
echo "Category map: {$mapPath}\n";

/**
 * @param  array<string, mixed>  $item
 */
function buildHandler(array $item): string
{
    $class = $item['class'];
    $key = $item['key'];
    $doc = str_replace('*/', '* /', $item['doc'] ?? $item['title']);
    $schemaCode = $item['schema_code'];
    $calcCode = $item['calc_code'];

    return <<<PHP
<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * {$doc}
 */
class {$class} extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return '{$key}';
    }

    public function inputSchema(): array
    {
{$schemaCode}
    }

    public function calculate(array \$inputs): array
    {
{$calcCode}
    }
}

PHP;
}

/**
 * @param  array<string, mixed>  $item
 */
function buildConverterHandler(array $item): string
{
    $class = $item['class'];
    $key = $item['key'];
    $doc = str_replace('*/', '* /', $item['doc'] ?? $item['title']);
    $factors = var_export($item['factors'], true);
    $from = $item['default_from'];
    $to = $item['default_to'];

    return <<<PHP
<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * {$doc}
 */
class {$class} extends AbstractCalculatorHandler
{
    protected const FACTORS = {$factors};

    public function key(): string
    {
        return '{$key}';
    }

    public function inputSchema(): array
    {
        \$units = array_keys(self::FACTORS);

        return [
            \$this->field('value', 'Value', 'number', ['min' => -1000000000000, 'max' => 1000000000000, 'step' => 0.000001, 'default' => 1]),
            \$this->field('from_unit', 'From Unit', 'select', ['options' => array_combine(\$units, \$units), 'default' => '{$from}']),
            \$this->field('to_unit', 'To Unit', 'select', ['options' => array_combine(\$units, \$units), 'default' => '{$to}']),
        ];
    }

    public function calculate(array \$inputs): array
    {
        \$value = \$this->requireNumeric(\$inputs, 'value');
        \$from = \$this->toString(\$inputs, 'from_unit');
        \$to = \$this->toString(\$inputs, 'to_unit');

        if (! isset(self::FACTORS[\$from], self::FACTORS[\$to])) {
            throw new InvalidArgumentException('Unsupported unit.');
        }

        \$base = \$value * self::FACTORS[\$from];
        \$converted = \$base / self::FACTORS[\$to];

        return [
            'results' => ['converted_value' => \$this->round(\$converted, 8)],
            'breakdown' => ['base_value' => \$this->round(\$base, 8), 'from_unit' => \$from, 'to_unit' => \$to],
            'units' => ['converted_value' => \$to],
        ];
    }
}

PHP;
}
