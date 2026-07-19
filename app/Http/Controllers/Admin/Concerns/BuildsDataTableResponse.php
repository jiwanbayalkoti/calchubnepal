<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared server-side DataTables pagination/search/sort logic so individual
 * admin controllers only need to supply a base query, the searchable
 * columns, and a row transformer.
 */
trait BuildsDataTableResponse
{
    /**
     * @param  array<int, string>  $searchableColumns  Real DB columns eligible for the global search box.
     * @param  array<int, string>  $orderableColumns  Column list matching the DataTables column order sent by the client.
     * @param  callable  $transform  function(mixed $model): array<string, mixed>
     */
    protected function toDataTableResponse(
        Request $request,
        Builder $query,
        array $searchableColumns,
        array $orderableColumns,
        callable $transform
    ): JsonResponse {
        $recordsTotal = $query->count();

        $search = (string) $request->input('search.value', '');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $recordsFiltered = $query->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $orderableColumns[$orderColumnIndex] ?? null;

        if ($orderColumn) {
            $query->orderBy($orderColumn, $orderDirection);
        }

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $rows = $query->get()->map($transform)->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
