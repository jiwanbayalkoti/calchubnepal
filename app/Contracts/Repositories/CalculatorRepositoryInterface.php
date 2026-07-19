<?php

namespace App\Contracts\Repositories;

use App\Models\Calculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence contract for the Calculator aggregate.
 *
 * Implementations must remain framework-idiomatic (Eloquent) but are kept
 * behind this interface so the service layer and controllers never depend
 * on a concrete ORM implementation directly.
 */
interface CalculatorRepositoryInterface
{
    public function find(int $id): ?Calculator;

    public function findBySlug(string $slug): ?Calculator;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Calculator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Calculator $calculator, array $data): Calculator;

    public function delete(Calculator $calculator): bool;

    /**
     * @return Collection<int, Calculator>
     */
    public function getRelated(Calculator $calculator, int $limit = 6): Collection;

    public function incrementUsage(Calculator $calculator): void;

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
    public function getByCategory(int $categoryId, int $limit = 0): Collection;
}
