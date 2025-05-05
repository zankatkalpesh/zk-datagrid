<?php

declare(strict_types=1);

namespace Zk\DataGrid;

use Illuminate\Support\Arr;

class Column
{

    public $path = '/';

    /**
     * Final output data.
     * 
     * @var array
     */
    protected ?array $output = null;

    /**
     * Create a column instance.
     */
    public function __construct(
        public int $index,
        public string $column,
        public string $type,
        public bool $eager = false,
        public mixed $alias = '',
        public mixed $title = null,
        public bool $sortable = false,
        public mixed $searchable = false,
        public mixed $filterable = false,
        public mixed $export = true,
        public mixed $filterParams = null,
        public mixed $formatter = null,
        public bool $escape = true,
        public array $attributes = [],
        public array $headingAttributes = [],
        public array $itemAttributes = [],
        public string $component = 'datagrid::column',
        public mixed $filterComponent = 'datagrid::filter',
    ) {
        $this->headingAttributes = (!empty($this->headingAttributes)) ? $this->headingAttributes : $this->attributes;
        $this->itemAttributes = (!empty($this->itemAttributes)) ? $this->itemAttributes : $this->attributes;
    }

    /**
     * Get index.
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Get column.
     * 
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Is eager.
     * 
     * @return bool
     */
    public function isEager(): bool
    {
        return $this->eager;
    }

    /**
     * Get relation path by removing the last segment.
     * 
     * @return string
     */
    public function getRelation(): string
    {
        if (str_contains($this->getAlias(), '.')) {
            $parts = explode('.', $this->getAlias());
            array_pop($parts); // remove the last segment
            return implode('.', $parts);
        }

        return '';
    }

    /**
     * Get relation column.
     * 
     * @return string
     */
    public function getRelationColumn(): string
    {
        if (str_contains($this->getAlias(), '.')) {
            $parts = explode('.', $this->getAlias());
            return end($parts);
        }

        return '';
    }

    /**
     * Get alias.
     * 
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias != '' ? $this->alias : $this->column;
    }

    /**
     * Get title.
     * 
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->title == null) {
            // Convert string to human readable
            $str = ucwords($this->getAlias(), '-');
            $str = ucwords($str, '_');
            $str = ucwords($str, '.');
            $this->title = str_replace(['-', '_', '.'], ' ', $str);
        }

        return $this->title;
    }

    /**
     * Get type.
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get filter params.
     * 
     * @return mixed
     */
    public function getFilterParams(): mixed
    {
        if (!$this->isFilterable()) {
            return [];
        }
        if ($this->filterParams == null) {
            return [
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Search by ' . $this->getTitle(),
                ],
            ];
        }

        // If options is callable, call it
        if (is_callable($this->filterParams)) {
            return call_user_func($this->filterParams, $this);
        }

        return $this->filterParams;
    }

    /**
     * is searchable.
     * 
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable != false;
    }

    /**
     * is searchable callback.
     * 
     * @return bool
     */
    public function isSearchableCallback(): bool
    {
        return is_callable($this->searchable);
    }

    /**
     * Get searchable.
     * 
     * @return mixed
     */
    public function getSearchable(): mixed
    {
        return $this->searchable;
    }

    /**
     * is filterable.
     * 
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable != false;
    }

    /**
     * is filterable callback.
     * 
     * @return bool
     */
    public function isFilterableCallback(): bool
    {
        return is_callable($this->filterable);
    }

    /**
     * Get filterable.
     * 
     * @return mixed
     */
    public function getFilterable(): mixed
    {
        return $this->filterable;
    }

    /**
     * is sortable.
     * 
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable != false;
    }

    /**
     * Get sortable link.
     * 
     * @return string
     */
    public function getSortableLink(): string
    {
        if (!$this->isSortable()) {
            return '';
        }
        $query = request()->query();
        $sort = $query['sort'] ?? ['column' => null, 'order' => null];
        $sort['order'] = ($sort['column'] == $this->getIndex() && $sort['order'] === 'asc') ? 'desc' : 'asc';
        $sort['column'] = $this->getIndex();
        $query['sort'] = $sort;

        return $this->path . '?' . Arr::query($query);
    }

    /**
     * is formatter.
     * 
     * @return bool
     */
    public function isFormatter(): bool
    {
        return $this->formatter != null && is_callable($this->formatter);
    }

    /**
     * Get formatter.
     * 
     * @return mixed
     */
    public function getFormatter(): mixed
    {
        return $this->formatter;
    }

    /**
     * is export.
     * 
     * @return bool
     */
    public function isExport(): bool
    {
        return $this->export != false;
    }

    /**
     * is export callback.
     * 
     * @return bool
     */
    public function isExportCallback(): bool
    {
        return is_callable($this->export);
    }

    /**
     * Get export formatter.
     * 
     * @return mixed
     */
    public function getExportFormatter(): mixed
    {
        return $this->export;
    }

    /**
     * is escape.
     * 
     * @return bool
     */
    public function isEscape(): bool
    {
        return $this->escape;
    }

    /**
     * Get heading attributes.
     * 
     * @return array
     */
    public function getHeadingAttributes(): array
    {
        return $this->headingAttributes;
    }

    /**
     * Get item attributes.
     * 
     * @return array
     */
    public function getItemAttributes(): array
    {
        return $this->itemAttributes;
    }

    /**
     * Get filter component.
     * 
     * @return string
     */
    public function getFilterComponent(): string
    {
        return $this->filterComponent;
    }

    /**
     * Get component.
     * 
     * @return string
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Transforms column to Array
     * 
     * @return array
     */
    public function toArray(): array
    {
        if ($this->output !== null) {
            return $this->output;
        }

        $this->output = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'column' => $this->getColumn(),
            // 'eager' => $this->isEager(),
            // 'relation' => $this->getRelation(),
            // 'relationColumn' => $this->getRelationColumn(),
            'alias' => $this->getAlias(),
            'title' => $this->getTitle(),
            'sortable' => $this->isSortable(),
            'sortableLink' => $this->getSortableLink(),
            'searchable' => $this->isSearchable(),
            'filterable' => $this->isFilterable(),
            'filterParams' => $this->getFilterParams(),
            'formatter' => $this->isFormatter(),
            'export' => $this->isExport(),
            'escape' => $this->isEscape(),
            'headingAttributes' => $this->getHeadingAttributes(),
            'itemAttributes' => $this->getItemAttributes(),
            'component' => $this->getComponent(),
            'filterComponent' => $this->getFilterComponent(),
        ];

        return $this->output;
    }
}
