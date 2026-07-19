<?php

namespace App\Services\Calculators;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Contracts\Services\CalculatorServiceInterface;
use App\Models\Calculator;
use App\Models\CalculationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CalculatorEngineService implements CalculatorServiceInterface
{
    public function __construct(
        protected CalculatorRepositoryInterface $repository,
        protected CalculatorRegistry $registry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $meta
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    public function calculate(string $slug, array $inputs, ?int $userId = null, array $meta = []): array
    {
        $calculator = $this->getBySlug($slug);

        if (! $this->registry->has($calculator->formula_key)) {
            Log::error('Calculator handler missing for formula key.', [
                'calculator_id' => $calculator->id,
                'formula_key' => $calculator->formula_key,
            ]);

            throw new RuntimeException("No handler registered for formula key [{$calculator->formula_key}].");
        }

        $handler = $this->registry->get($calculator->formula_key);

        try {
            $result = $handler->calculate($inputs);
        } catch (Throwable $e) {
            Log::error('Calculator computation failed.', [
                'calculator' => $calculator->slug,
                'formula_key' => $calculator->formula_key,
                'inputs' => $inputs,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Unable to process this calculation: '.$e->getMessage(), 0, $e);
        }

        $result['results'] ??= [];
        $result['breakdown'] ??= [];
        $result['units'] ??= [];

        $this->persistCalculation($calculator, $inputs, $result, $userId, $meta);

        return $result;
    }

    public function getBySlug(string $slug): Calculator
    {
        $calculator = $this->repository->findBySlug($slug);

        if (! $calculator) {
            throw new RuntimeException("Calculator [{$slug}] could not be found.");
        }

        return $calculator;
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getPopular(int $limit = 10): Collection
    {
        return $this->repository->getPopular($limit);
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->repository->getFeatured($limit);
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getRelated(Calculator $calculator, int $limit = 6): Collection
    {
        return $this->repository->getRelated($calculator, $limit);
    }

    /**
     * Persist usage counters and calculation history inside a single
     * transaction. Failures here are logged but must never bubble up and
     * break the calculation response for the end user.
     *
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $meta
     */
    protected function persistCalculation(Calculator $calculator, array $inputs, array $result, ?int $userId, array $meta): void
    {
        try {
            DB::transaction(function () use ($calculator, $inputs, $result, $userId, $meta) {
                $this->repository->incrementUsage($calculator);

                $explanation = $result['explanation'] ?? ($result['breakdown'] ?? null);

                if (is_array($explanation)) {
                    $explanation = json_encode($explanation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }

                CalculationHistory::create([
                    'user_id' => $userId,
                    'calculator_id' => $calculator->id,
                    'inputs' => $inputs,
                    'outputs' => $result,
                    'explanation' => $explanation,
                    'ip_address' => $meta['ip_address'] ?? null,
                    'user_agent' => $meta['user_agent'] ?? null,
                    'created_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Failed to persist calculation history.', [
                'calculator' => $calculator->slug,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
