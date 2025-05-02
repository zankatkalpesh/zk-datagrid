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
        $searchColumns = collect($columns)->filter(
            fn($column) => $column->isSearchable() && $column->getColumn()
        );

        if ($searchColumns->isEmpty()) {
            return;
        }

        $this->query->where(function (Builder $query) use ($search, $searchColumns) {
            // 1. Custom callback search
            $searchColumns->filter(fn($col) => $col->isSearchableCallback())
                ->each(fn($col) => $col->getSearchable()($query, $search, $col));

            // 2. Handle eager-loaded relationships
            $searchColumns->filter(fn($col) => $col->isEager())
                ->groupBy(fn($col) => $col->getRelation())
                ->each(function ($cols, $relation) use ($query, $search) {
                    $query->orWhereHas($relation, function (Builder $query) use ($cols, $search) {
                        $query->where(function (Builder $query) use ($cols, $search) {
                            foreach ($cols as $col) {
                                $colName = $col->getRelationColumn();
                                $type = $col->getType();
                                $this->applySearchCondition($query, $colName, $type, $search);
                            }
                        });
                    });
                });

            // 3. Direct column search
            $searchColumns->filter(fn($col) => !$col->isEager() && !$col->isSearchableCallback())
                ->each(function ($col) use ($query, $search) {
                    $colName = $col->getColumn();
                    $type = $col->getType();
                    $this->applySearchCondition($query, $colName, $type, $search);
                });
        });
    }

    /**
     * Apply search condition based on column type.
     */
    public function applySearchCondition(Builder $query, string $colName, string $type, $search): void
    {
        $method = match ($type) {
            'number', 'integer' => 'orWhere',
            'date' => 'orWhereDate',
            'string-cs' => fn($q, $col, $val) => $q->orWhere($col, 'like', '%' . $val . '%'),
            'fulltext' => fn($q, $col, $val) => $q->orWhereRaw("MATCH(" . $col . ") AGAINST (? IN BOOLEAN MODE)", [$val]),
            default => fn($q, $col, $val) => $q->orWhere(DB::raw('LOWER(' . $col . ')'), 'like', '%' . strtolower($val) . '%'),
        };

        is_callable($method)
            ? $method($query, $colName, $search)
            : $query->{$method}($colName, $search);
    }

    /**
     * @param array $filters
     * @param Column[] $columns
     */
    public function filters(array $filters, $columns): void
    {
        $filterColumns = collect($columns)->filter(
            fn($col) =>
            $col->isFilterable()
                && trim($col->getColumn()) !== ''
                && isset($filters[$col->getIndex()])
                && $filters[$col->getIndex()] !== ''
        );

        if ($filterColumns->isEmpty()) {
            return;
        }

        $this->query->where(function (Builder $query) use ($filters, $filterColumns) {
            // 1. Custom callback filters
            $filterColumns->filter(fn($col) => $col->isFilterableCallback())
                ->each(fn($col) => $col->getFilterable()($query, $filters[$col->getIndex()], $col));

            // 2. Eager relationship filters
            $filterColumns->filter(fn($col) => $col->isEager())
                ->groupBy(fn($col) => $col->getRelation())
                ->each(function ($cols, $relation) use ($query, $filters) {
                    $query->whereHas($relation, function (Builder $query) use ($cols, $filters) {
                        $query->where(function (Builder $query) use ($cols, $filters) {
                            foreach ($cols as $col) {
                                $colName = $col->getRelationColumn();
                                $type = $col->getType();
                                $filter = $filters[$col->getIndex()];
                                $this->applyFilterCondition($query, $colName, $type, $filter, true);
                            }
                        });
                    });
                });

            // 3. Direct column filters
            $filterColumns->filter(fn($col) => !$col->isEager() && !$col->isFilterableCallback())
                ->each(function ($col) use ($query, $filters) {
                    $colName = $col->getColumn();
                    $type = $col->getType();
                    $filter = $filters[$col->getIndex()];
                    $this->applyFilterCondition($query, $colName, $type, $filter);
                });
        });
    }

    /**
     * Apply filter condition based on column type.
     */
    public function applyFilterCondition(Builder $query, string $colName, string $type, $filter): void
    {
        $method = match ($type) {
            'number', 'integer' => 'orWhere',
            'date' => 'orWhereDate',
            'string-cs' => fn($query, $colName, $value) => $query->orWhere($colName, 'like', '%' . $value . '%'),
            'fulltext' => fn($q, $col, $val) => $q->orWhereRaw("MATCH(" . $col . ") AGAINST (? IN BOOLEAN MODE)", [$val]),
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
     * All items
     */
    public function all(): mixed
    {
        return $this->query->get();
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
