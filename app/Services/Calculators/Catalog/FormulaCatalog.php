<?php

namespace App\Services\Calculators\Catalog;

/**
 * In-memory catalog of formula definitions for calculators that share a
 * generic, configuration-driven engine (see ConfigurableCalculatorHandler)
 * instead of a bespoke handler class. Backed by the definitions.php data
 * file so new calculators can be activated by adding a config entry
 * rather than writing a new PHP class.
 */
class FormulaCatalog
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected static ?array $definitions = null;

    public function has(string $key): bool
    {
        return array_key_exists($key, self::load());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        return self::load()[$key] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys(self::load());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return self::load();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function load(): array
    {
        if (self::$definitions === null) {
            /** @var array<string, array<string, mixed>> $definitions */
            $definitions = require __DIR__.'/definitions.php';
            self::$definitions = $definitions;
        }

        return self::$definitions;
    }
}
