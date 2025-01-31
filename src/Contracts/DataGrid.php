<?php

namespace Zk\DataGrid\Contracts;

interface DataGrid
{
    /**
     * Prepare items.
     */
    public function prepareItems(): void;

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void;
}
