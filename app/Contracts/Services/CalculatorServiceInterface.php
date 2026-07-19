<?php

namespace App\Contracts\Services;

use App\Models\Calculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Application service contract orchestrating calculator lookups and
 * formula execution. Controllers (web + API) must depend on this
 * interface rather than the concrete CalculatorEngineService so the
 * implementation can be swapped/mocked freely.
 */
interface CalculatorServiceInterface
{
    /**
     * Resolve the calculator by slug, run its formula handler against the
     * supplied inputs, persist usage/history side effects, and return the
     * calculation result.
     *
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $meta  Optional context (ip_address, user_agent, save, title, notes...)
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    public function calculate(string $slug, array $inputs, ?int $userId = null, array $meta = []): array;

    public function getBySlug(string $slug): Calculator;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Calculator>
     */
    public function getPopular(int $limit = 10): Collection;

    /**
     * @return Collection<int, Calculator>
     */
    public function getFeatured(int $limit = 10): Collection;

    /**
     * @return Collection<int, Calculator>
     */
    public function getRelated(Calculator $calculator, int $limit = 6): Collection;
}
