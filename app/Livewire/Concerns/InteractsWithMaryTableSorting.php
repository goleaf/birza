<?php

namespace App\Livewire\Concerns;

trait InteractsWithMaryTableSorting
{
    /**
     * @param  array{column?: mixed, direction?: mixed}  $sortBy
     * @param  list<string>  $allowedColumns
     * @return array{column: string, direction: string}
     */
    protected function normalizeSortBy(array $sortBy, array $allowedColumns, string $defaultColumn, string $defaultDirection = 'desc'): array
    {
        $column = is_string($sortBy['column'] ?? null) ? $sortBy['column'] : $defaultColumn;
        $direction = is_string($sortBy['direction'] ?? null) ? $sortBy['direction'] : $defaultDirection;

        if (! in_array($column, $allowedColumns, true)) {
            $column = $defaultColumn;
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        return [
            'column' => $column,
            'direction' => $direction,
        ];
    }

    /**
     * @param  list<string>  $allowedColumns
     * @return array{column: string, direction: string}
     */
    protected function sortByFromString(?string $sort, array $allowedColumns, string $defaultColumn, string $defaultDirection = 'desc'): array
    {
        $parts = explode(',', (string) $sort);

        return $this->normalizeSortBy([
            'column' => $parts[0] ?? null,
            'direction' => $parts[1] ?? null,
        ], $allowedColumns, $defaultColumn, $defaultDirection);
    }

    /**
     * @param  array{column: string, direction: string}  $sortBy
     */
    protected function sortString(array $sortBy): string
    {
        return $sortBy['column'].','.$sortBy['direction'];
    }
}
