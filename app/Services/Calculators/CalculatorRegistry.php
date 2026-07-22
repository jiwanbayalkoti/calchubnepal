<?php

namespace App\Services\Calculators;

use App\Contracts\Calculators\CalculatorHandlerInterface;
use App\Models\Calculator;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Central registry of all available calculator formula handlers.
 *
 * Handlers are auto-discovered from the Handlers/ directory: any concrete
 * class implementing CalculatorHandlerInterface is instantiated through
 * the container and registered under its key(). This means adding a new
 * calculator to the platform only requires dropping a new handler class
 * into app/Services/Calculators/Handlers - no manual registration needed.
 */
class CalculatorRegistry
{
    /**
     * @var array<string, CalculatorHandlerInterface>
     */
    protected array $handlers = [];

    protected bool $discovered = false;

    public function __construct(protected Container $container)
    {
    }

    public function register(CalculatorHandlerInterface $handler): void
    {
        $this->handlers[$handler->key()] = $handler;
    }

    public function has(string $key): bool
    {
        $this->discoverIfNeeded();

        if (isset($this->handlers[$key])) {
            return true;
        }

        // Allow catalog stubs that exist in DB but have no dedicated handler yet.
        return Calculator::query()->where('formula_key', $key)->where('is_active', true)->exists();
    }

    public function get(string $key): CalculatorHandlerInterface
    {
        $this->discoverIfNeeded();

        if (isset($this->handlers[$key])) {
            return $this->handlers[$key];
        }

        if (Calculator::query()->where('formula_key', $key)->where('is_active', true)->exists()) {
            return new DynamicStubHandler($key);
        }

        throw new InvalidArgumentException("No calculator handler registered for key [{$key}].");
    }

    /**
     * @return array<string, CalculatorHandlerInterface>
     */
    public function all(): array
    {
        $this->discoverIfNeeded();

        return $this->handlers;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        $this->discoverIfNeeded();

        return array_keys($this->handlers);
    }

    protected function discoverIfNeeded(): void
    {
        if ($this->discovered) {
            return;
        }

        $this->discovered = true;
        $this->discoverHandlers();
    }

    protected function discoverHandlers(): void
    {
        $directory = app_path('Services/Calculators/Handlers');

        if (! is_dir($directory)) {
            return;
        }

        $files = glob($directory.DIRECTORY_SEPARATOR.'*.php') ?: [];

        foreach ($files as $file) {
            $class = 'App\\Services\\Calculators\\Handlers\\'.basename($file, '.php');

            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, CalculatorHandlerInterface::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            /** @var CalculatorHandlerInterface $handler */
            $handler = $this->container->make($class);

            $this->register($handler);
        }
    }
}
