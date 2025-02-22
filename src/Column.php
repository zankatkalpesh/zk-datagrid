<?php

declare(strict_types=1);

namespace Zk\DataGrid;

use Illuminate\Support\Arr;

class Column
{

    use Traits\GeneralMethods;

    public $path = '/';

    /**
     * Final output data.
     * 
     * @var array
     */
    protected ?array $output = null;

    /**
     * Heading attributes string.
     * 
     * @var string
     */
    protected string $headingAttrStr;

    /**
     * Item attributes string.
     * 
     * @var string
     */
    protected string $itemAttrStr;

    /**
     * Create a column instance.
     */
    public function __construct(
        public int $index,
        public string $column,
        public string $type,
        public mixed $title = null,
        public bool $sortable = false,
        public mixed $searchable = false,
        public mixed $filterable = false,
        public mixed $options = null,
        public mixed $formatter = null,
        public bool $escape = true,
        public array $attributes = [],
        public array $headingAttributes = [],
        public array $itemAttributes = [],
        public mixed $filterComponent = 'datagrid::filter',
    ) {
        $this->headingAttributes = (!empty($this->headingAttributes)) ? $this->headingAttributes : $this->attributes;
        $this->itemAttributes = (!empty($this->itemAttributes)) ? $this->itemAttributes : $this->attributes;

        $this->headingAttrStr = $this->printAttributes($this->headingAttributes, ['class']);
        $this->itemAttrStr = $this->printAttributes($this->itemAttributes);
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
     * Get title.
     * 
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->title == null) {
            // Convert string to human readable
            $str = ucwords($this->column, '-');
            $str = ucwords($str, '_');
            $this->title = str_replace(['-', '_'], ' ', $str);
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
     * Get options.
     * 
     * @return mixed
     */
    public function getOptions(): mixed
    {
        if (!$this->isFilterable()) {
            return [];
        }
        if ($this->options == null) {
            return [
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Search by ' . $this->getTitle(),
                ],
            ];
        }

        // If options is callable, call it
        if (is_callable($this->options)) {
            return call_user_func($this->options, $this);
        }

        return $this->options;
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
     * Get heading attributes string.
     * 
     * @return string
     */
    public function getHeadingAttributesString(): string
    {
        return $this->headingAttrStr;
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
     * Get item attributes string.
     * 
     * @return string
     */
    public function getItemAttributesString(): string
    {
        return $this->itemAttrStr;
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
            'column' => $this->getColumn(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
            'sortable' => $this->isSortable(),
            'sortableLink' => $this->getSortableLink(),
            'searchable' => $this->isSearchable(),
            'filterable' => $this->isFilterable(),
            'options' => $this->getOptions(),
            'formatter' => $this->isFormatter(),
            'escape' => $this->isEscape(),
            'headingAttributes' => $this->getHeadingAttributes(),
            'headingAttributesString' => $this->getHeadingAttributesString(),
            'itemAttributes' => $this->getItemAttributes(),
            'itemAttributesString' => $this->getItemAttributesString(),
            'filterComponent' => $this->getFilterComponent(),
        ];

        return $this->output;
    }
}
