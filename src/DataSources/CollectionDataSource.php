<?php

declare(strict_types=1);

namespace Zk\DataGrid\DataSources;

use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Zk\DataGrid\Contracts\DataSource;
use Zk\DataGrid\Column;

class CollectionDataSource implements DataSource
{

    public Collection $data;

    public Collection $processedData;

    /**
     * @param Collection $data
     */
    public function __construct(Collection $data)
    {
        $this->data = $data;
        $this->processedData = $data;
    }

    /**
     * @param string $search
     * @param Column[] $columns
     */
    public function search(string $search, $columns): void
    {
        $searchColumns = collect($columns)->filter(function ($column) {
            return $column->isSearchable() && $column->getColumn() !== '';
        });

        if ($searchColumns->isEmpty()) {
            return;
        }

        $this->processedData = $this->data->filter(function ($item) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $colName = $column->getColumn();
                if (is_array($item)) {
                    $columnValue = $item[$colName];
                }

                if (is_object($item)) {
                    $columnValue = $item->{$colName};
                }

                if ($column->isSearchableCallback()) {
                    return $column->getSearchable()($item, $columnValue, $search, $column);
                }
                if (Str::contains($columnValue, $search)) {
                    return true;
                };
            }
        });
    }

    /**
     * @param array $filters
     * @param Column[] $columns
     */
    public function filters(array $filters, $columns): void
    {
        $filterColumns = collect($columns)->filter(function ($column) use ($filters) {
            if ($column->getColumn() === '') return false;

            $colIndex = $column->getIndex();
            return $column->isFilterable() && isset($filters[$colIndex]) && $filters[$colIndex] !== '';
        });

        if ($filterColumns->isEmpty()) {
            return;
        }

        $this->processedData = $this->processedData->filter(function ($item) use ($filters, $filterColumns) {
            $isMatch = true;
            foreach ($filterColumns as $column) {
                $colIndex = $column->getIndex();
                $colName = $column->getColumn();
                $filter = $filters[$colIndex] ?? null;

                if (is_array($item)) {
                    $columnValue = $item[$colName];
                }

                if (is_object($item)) {
                    $columnValue = $item->{$colName};
                }

                if ($column->isFilterableCallback()) {
                    $isMatch = $isMatch && $column->getFilterable()($item, $columnValue, $filter, $column);
                } elseif (is_array($filter)) {
                    $isMatch = $isMatch && collect($filter)->filter(function ($value) use ($columnValue) {
                        return Str::contains($columnValue, $value);
                    })->isNotEmpty();
                } else {
                    $isMatch = $isMatch && Str::contains($columnValue, $filter);
                }

                if (!$isMatch) {
                    break;
                }
            }
            return $isMatch;
        });
    }

    /**
     * @param array $columns
     * @param array $orders
     */
    public function sort(array $columns, array $orders): void
    {
        collect($columns)->each(function ($column, $index) use ($orders) {
            if ($column instanceof Column) {
                $column = $column->getColumn();
            }
            $order = ($orders[$index] ?? 'asc') === 'asc' ? 'asc' : 'desc';
            $this->processedData = $this->processedData->sortBy($column, SORT_REGULAR, $order === 'desc');
        });
    }

    /**
     * All items
     */
    public function all(): Collection
    {
        return $this->processedData;
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     */
    public function paginate($perPage, $columns = ['*'], $pageName = 'page', $page = null): Paginator
    {
        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

        $results = ($total = $this->processedData->count())
            ? $this->processedData->skip(($page - 1) * $perPage)->take($perPage)
            : collect();

        $results = collect($results->values());

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => '/',
            'pageName' => $pageName,
        ]);
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }
}
