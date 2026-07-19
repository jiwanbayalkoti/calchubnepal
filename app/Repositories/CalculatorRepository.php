<?php

namespace App\Repositories;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Models\Calculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CalculatorRepository implements CalculatorRepositoryInterface
{
    public function __construct(protected Calculator $model)
    {
    }

    public function find(int $id): ?Calculator
    {
        return $this->baseQuery()->find($id);
    }

    public function findBySlug(string $slug): ?Calculator
    {
        return $this->baseQuery()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);

        return $query->ordered()->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Calculator
    {
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Calculator $calculator, array $data): Calculator
    {
        $calculator->update($data);

        return $calculator->refresh();
    }

    public function delete(Calculator $calculator): bool
    {
        return (bool) $calculator->delete();
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getRelated(Calculator $calculator, int $limit = 6): Collection
    {
        $relatedIds = $calculator->relatedCalculators()->pluck('calculators.id');

        if ($relatedIds->isNotEmpty()) {
            $related = $this->baseQuery()
                ->whereIn('id', $relatedIds)
                ->active()
                ->limit($limit)
                ->get();

            if ($related->count() >= $limit) {
                return $related;
            }
        } else {
            $related = new Collection;
        }

        $existingIds = $related->pluck('id')->push($calculator->id)->all();

        $fallback = $this->baseQuery()
            ->where('calculator_category_id', $calculator->calculator_category_id)
            ->whereNotIn('id', $existingIds)
            ->active()
            ->orderByDesc('usage_count')
            ->limit($limit - $related->count())
            ->get();

        return $related->concat($fallback)->values();
    }

    public function incrementUsage(Calculator $calculator): void
    {
        $calculator->increment('usage_count');
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->active()
            ->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('meta_keywords', 'like', "%{$term}%");
            })
            ->orderByDesc('usage_count')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getPopular(int $limit = 10): Collection
    {
        return $this->baseQuery()
            ->active()
            ->orderByDesc('usage_count')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->baseQuery()
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Calculator>
     */
    public function getByCategory(int $categoryId, int $limit = 0): Collection
    {
        $query = $this->baseQuery()
            ->where('calculator_category_id', $categoryId)
            ->active()
            ->ordered();

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    protected function baseQuery(): Builder
    {
        return $this->model->newQuery()->with(['category', 'faqs' => function ($query) {
            $query->active();
        }, 'examples']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['category_id'])) {
            $query->where('calculator_category_id', $filters['category_id']);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', $filters['is_active']);
        }

        if (array_key_exists('is_featured', $filters)) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (array_key_exists('is_premium', $filters)) {
            $query->where('is_premium', $filters['is_premium']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%");
            });
        }
    }
}
