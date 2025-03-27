<?php

declare(strict_types=1);

namespace Zk\DataGrid\DataSources;

use Zk\DataGrid\Contracts\DataSource;

class ArrayDataSource extends CollectionDataSource implements DataSource
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct(collect($data));
    }
}
