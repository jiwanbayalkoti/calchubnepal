<?php

function num(string $name, string $label, array $opts = []): string
{
    $min = $opts['min'] ?? 0;
    $max = $opts['max'] ?? 1000000000;
    $step = $opts['step'] ?? 0.01;
    $default = $opts['default'] ?? 1;
    $unit = isset($opts['unit']) ? ", 'unit' => '{$opts['unit']}'" : '';
    $required = isset($opts['required']) && $opts['required'] === false ? ", 'required' => false" : '';

    return "            \$this->field('{$name}', '{$label}', 'number', ['min' => {$min}, 'max' => {$max}, 'step' => {$step}, 'default' => {$default}{$unit}{$required}]),";
}

function sel(string $name, string $label, array $options, string $default): string
{
    $parts = [];
    foreach ($options as $k => $v) {
        $parts[] = "'{$k}' => '".addslashes($v)."'";
    }
    $opt = implode(', ', $parts);

    return "            \$this->field('{$name}', '{$label}', 'select', ['options' => [{$opt}], 'default' => '{$default}']),";
}

function schema(array $lines): string
{
    return "        return [\n".implode("\n", $lines)."\n        ];";
}

function item(string $key, string $class, string $category, string $title, string $schemaCode, string $calcCode, ?string $doc = null): array
{
    return [
        'key' => $key,
        'class' => $class,
        'category' => $category,
        'title' => $title,
        'doc' => $doc ?? $title,
        'schema_code' => $schemaCode,
        'calc_code' => $calcCode,
    ];
}

function converterItem(string $key, string $class, string $category, string $title, array $factors, string $defaultFrom, string $defaultTo): array
{
    return [
        'key' => $key,
        'class' => $class,
        'category' => $category,
        'title' => $title,
        'doc' => $title,
        'type' => 'converter',
        'factors' => $factors,
        'default_from' => $defaultFrom,
        'default_to' => $defaultTo,
    ];
}
