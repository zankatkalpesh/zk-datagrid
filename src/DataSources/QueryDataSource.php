<?php

declare(strict_types=1);

namespace Zk\DataGrid\DataSources;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Zk\DataGrid\Column;
use Zk\DataGrid\Contracts\DataSource;

class QueryDataSource implements DataSource
{
    public Builder $query;

    /**
     * @param Builder $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @param mixed $search
     * @param Column[] $columns
     */
    public function search(mixed $search, $columns): void
    {
        $searchColumns = collect($columns)->filter(function ($column) {
            return $column->isSearchable() && $column->getColumn() !== '';
        });

        if ($searchColumns->isEmpty()) {
            return;
        }

        $this->query->where(function (Builder $query) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $colName = $column->getColumn();
                if ($column->isSearchableCallback()) {
                    $column->getSearchable()($query, $search, $column);
                    continue;
                } else {
                    $type = $column->getType();
                    match ($type) {
                        'number', 'integer' => $query->orWhere($colName, $search),
                        'date' => $query->orWhereDate($colName, $search),
                        'string-cs' => $query->orWhere($colName, 'like', "%{$search}%"),
                        default => $query->orWhere(DB::raw("LOWER({$colName})"), 'like', "%" . strtolower($search) . "%"),
                    };
                }
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

        $this->query->where(function (Builder $query) use ($filters, $filterColumns) {
            foreach ($filterColumns as $column) {
                $colIndex = $column->getIndex();
                $colName = $column->getColumn();
                $filter = $filters[$colIndex] ?? null;
                if ($column->isFilterableCallback()) {
                    $column->getFilterable()($query, $filter, $column);
                    continue;
                }

                $type = $column->getType();
                $method = match ($type) {
                    'number', 'integer' => 'orWhere',
                    'date' => 'orWhereDate',
                    'string-cs' => fn($query, $colName, $value) => $query->orWhere($colName, 'like', '%' . $value . '%'),
                    default => fn($query, $colName, $value) => $query->orWhere(DB::raw('LOWER(' . $colName . ')'), 'like', '%' . strtolower($value) . '%'),
                };

                $query->where(function (Builder $query) use ($filter, $colName, $method) {
                    foreach ((array) $filter as $value) {
                        is_callable($method)
                            ? $method($query, $colName, $value)
                            : $query->{$method}($colName, $value);
                    }
                });
            }
        });
    }

    /**
     * @param array $columns
     * @param array $orders
     */
    public function sort(array $columns, array $orders): void
    {
        // Remove existing, default ordering
        $this->query->reorder();

        collect($columns)->each(function ($column, $index) use ($orders) {
            $order = ($orders[$index] ?? 'asc') === 'asc' ? 'asc' : 'desc';
            $this->query->orderBy($column, $order);
        });
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     */
    public function paginate($perPage, $columns = ['*'], $pageName = 'page', $page = null): Paginator
    {
        return $this->query->paginate(
            $perPage,
            $columns,
            $pageName,
            $page
        )->withPath('/');
    }
}
