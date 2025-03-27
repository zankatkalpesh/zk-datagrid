<?php

namespace Zk\DataGrid\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Zk\DataGrid\Column;

interface DataSource
{
    /**
     * @param string $search
     * @param Column[] $columns
     */
    public function search(string $search, $columns): void;

    /**
     * @param array $filters
     * @param Column[] $columns
     */
    public function filters(array $filters, $columns): void;

    /**
     * @param array $columns
     * @param array $orders
     */
    public function sort(array $columns, array $orders): void;

    /**
     * All items
     */
    public function all(): mixed;

    /**
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     */
    public function paginate($perPage, $columns = [], $pageName = '', $page = null): Paginator;
}
