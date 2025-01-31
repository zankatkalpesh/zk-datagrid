<?php

declare(strict_types=1);

namespace Zk\DataGrid\DataSources;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
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
            return $column->isSearchable();
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
                } else{
                    $query->orWhere($colName, 'like', '%' . $search . '%');
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
                if (is_array($filter)) {
                    $query->where(function (Builder $query) use ($filter, $colName) {
                        foreach ($filter as $value) {
                            $query->orWhere($colName, 'like', '%' . $value . '%');
                        }
                    });
                    continue;
                }
                $query->where($colName, 'like', '%' . $filter . '%');
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
