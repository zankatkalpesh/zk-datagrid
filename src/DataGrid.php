<?php

declare(strict_types=1);

namespace Zk\DataGrid;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Zk\DataGrid\Contracts\DataSource;
use Zk\DataGrid\DataSources\QueryDataSource;
use Zk\DataGrid\DataSources\CollectionDataSource;
use Zk\DataGrid\DataSources\ArrayDataSource;
use Illuminate\Support\Str;

class DataGrid
{
    /**
     * Name of primary column.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Default sort enabled. 
     *
     * @var bool
     */
    public $defaultSort = true;

    /**
     * Default sort column of datagrid.
     *
     * @var ?mixed
     */
    public $sortColumn;

    /**
     * Default sort order of datagrid.
     *
     * @var mixed
     */
    public $sortOrder = 'asc';

    /**
     * Grid name.
     *
     * @var string
     */
    protected string $gridName;

    /**
     * Unique identifier string.
     * 
     * @var string
     */
    protected ?string $uid = null;

    /**
     * Endpoint base url.
     * 
     * @var string
     */
    protected ?string $baseUrl = null;

    /**
     * Default items per page.
     *
     * @var int
     */
    protected $itemsPerPage = 15;

    /**
     * Default max items per page.
     * 
     * @var int
     */
    protected $maxItemsPerPage = 1000;

    /**
     * Default per page options.
     * 
     * @var array|null
     */
    protected $perPageOptions = [15, 25, 50, 75, 100];

    /**
     * Columns.
     *
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Actions.
     *
     * @var Action[]
     */
    protected $actions = [];

    /**
     * Mass action.
     *
     * @var MassAction[]
     */
    protected $massActions = [];

    /**
     * Has search enabled.
     * 
     * @var bool
     */
    protected $searchEnabled = false;

    /**
     * Query builder.
     * 
     * @var Builder
     */
    protected Builder $query;

    /**
     * Data source.
     * 
     * @var DataSource
     */
    protected DataSource $dataSource;

    /**
     * Final request data.
     * 
     * @var array
     */
    protected ?array $requestData = null;

    /**
     * Final output data.
     * 
     * @var array
     */
    protected ?array $output = null;

    /**
     * Mass action title text.
     * 
     * @var string
     */
    protected string $massActionTitle = 'Select action';

    /**
     * Empty text.
     * 
     * @var string
     */
    protected string $emptyText = 'No records found';

    /**
     * Loading text.
     * 
     * @var string
     */
    protected string $loadingText = 'Loading...';

    /**
     * Meta data
     *
     * @var array
     */
    protected $metaData = [];

    /**
     * Set primary key.
     * 
     * @param string $primaryKey
     * @return self
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Create a datagrid instance from query.
     * 
     * @param Builder $query
     * @return self
     */
    public function fromQuery(Builder $query): self
    {
        $this->dataSource = new QueryDataSource($query);

        return $this;
    }

    /**
     * Create a datagrid instance from collection.
     * 
     * @param Collection $data
     * @return self
     */
    public function fromCollection(Collection $data): self
    {
        $this->dataSource = new CollectionDataSource($data);

        return $this;
    }

    /**
     * Create a datagrid instance from array.
     * 
     * @param array $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        $this->dataSource = new ArrayDataSource($data);

        return $this;
    }

    /**
     * Get Grid Unique Identifier.
     *
     * @return string
     */
    public function getUid(): string
    {
        if ($this->uid === null) {
            $this->uid = Str::slug($this->getGridName() . '-' . Str::random(5));
        }
        return $this->uid;
    }

    /**
     * Set Grid Unique Identifier.
     *
     * @param string $uid
     * @return self
     */
    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get base url.
     * 
     * @return string
     */
    public function getBaseUrl(): string
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = request()->url();
        }

        return $this->baseUrl;
    }

    /**
     * Set base url.
     * 
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Set custom data source.
     * 
     * @param DataSource $dataSource
     * @return self
     */
    public function setDataSource(DataSource $dataSource): self
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * Set Items per page.
     * 
     * @param int $itemsPerPage
     * @return self
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * Set Max Items per page.
     * 
     * @param int $maxItemsPerPage
     * @return self
     */
    public function setMaxItemsPerPage(int $maxItemsPerPage): self
    {
        $this->maxItemsPerPage = $maxItemsPerPage;

        return $this;
    }

    /**
     * Set Per Page Options.
     * 
     * @param array|null $perPageOptions
     * @return self
     */
    public function setPerPageOptions(array|null $perPageOptions): self
    {
        $this->perPageOptions = $perPageOptions;

        return $this;
    }

    /**
     * Set search enabled.
     * 
     * @param bool $searchEnabled
     * @return self
     */
    public function setSearchEnabled(bool $searchEnabled): self
    {
        $this->searchEnabled = $searchEnabled;

        return $this;
    }

    /** 
     * Get mass action title.
     * 
     * @return string
     */
    public function getMassActionTitle(): string
    {
        return $this->massActionTitle;
    }

    /**
     * Set mass action title.
     * 
     * @param string $massActionTitle
     * @return self
     */
    public function setMassActionTitle(string $massActionTitle): self
    {
        $this->massActionTitle = $massActionTitle;

        return $this;
    }

    /**
     * Set empty text.
     * 
     * @param string $emptyText
     * @return self
     */
    public function setEmptyText(string $emptyText): self
    {
        $this->emptyText = $emptyText;

        return $this;
    }


    /**
     * Set loading text.
     * 
     * @param string $loadingText
     * @return self
     */
    public function setLoadingText(string $loadingText): self
    {
        $this->loadingText = $loadingText;

        return $this;
    }

    /**
     * Get Meta Data
     * 
     * @param string | null $key
     * @return mixed
     */
    public function getMetaData($key = null)
    {
        return ($key) ? Arr::get($this->metaData, $key) : $this->metaData;
    }

    /** 
     * Set properties
     * 
     * @param array $metaData
     * @return Form
     */
    public function setMetaData(array $metaData)
    {
        $this->metaData = array_merge($this->metaData, $metaData);

        return $this;
    }

    /**
     * Map items to array.
     * 
     * @param array $items
     */
    private function mapToArray(array $items): array
    {
        return LazyCollection::make($items)
            ->map(fn($item) => $item->toArray())
            ->all();
    }

    /**
     * Get columns.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /** 
     * Get columns as array.
     * 
     * @return array
     */
    public function getColumnsArray(): array
    {
        return $this->mapToArray($this->columns);
    }

    /**
     * Get actions.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get actions as array.
     * 
     * @return array
     */
    public function getActionsArray(): array
    {
        return $this->mapToArray($this->actions);
    }

    /**
     * Get mass actions.
     */
    public function getMassActions(): array
    {
        return $this->massActions;
    }

    /**
     * Get mass actions as array.
     * 
     * @return array
     */
    public function getMassActionsArray(): array
    {
        return $this->mapToArray($this->massActions);
    }

    /**
     * Add column.
     * 
     * @param array $column
     */
    public function addColumn(array $column): void
    {
        $defaults = [
            'type' => 'string',
            'title' => null,
            'sortable' => false,
            'searchable' => false,
            'filterable' => false,
            'options' => null,
            'formatter' => null,
            'escape' => true,
            'attributes' => [],
            'headingAttributes' => [],
            'itemAttributes' => [],
        ];

        $column = array_merge($defaults, $column);

        if ($column['type'] === 'serial-no') {
            $column['column'] = '';
            $column['sortable'] = false;
            $column['searchable'] = false;
            $column['filterable'] = false;
        }

        $this->columns[] = new Column(
            index: count($this->columns),
            column: $column['column'],
            type: $column['type'],
            title: $column['title'],
            sortable: $column['sortable'],
            searchable: $column['searchable'],
            filterable: $column['filterable'],
            options: $column['options'],
            formatter: $column['formatter'],
            escape: $column['escape'],
            attributes: $column['attributes'],
            headingAttributes: $column['headingAttributes'],
            itemAttributes: $column['itemAttributes']
        );

        if ($column['searchable'] ?? false) {
            $this->searchEnabled = true;
        }
    }

    /**
     * Add action.
     */
    public function addAction(array $action): void
    {
        $defaults = [
            'icon' => '',
            'method' => '',
            'url' => '',
            'formatter' => null,
            'escape' => true,
            'attributes' => [],
        ];

        $action = array_merge($defaults, $action);

        $this->actions[] = new Action(
            index: count($this->actions),
            title: $action['title'],
            icon: $action['icon'],
            method: $action['method'],
            url: $action['url'],
            formatter: $action['formatter'],
            escape: $action['escape'],
            attributes: $action['attributes']
        );
    }

    /**
     * Add mass action.
     */
    public function addMassAction(array $massAction): void
    {
        $defaults = [
            'value' => null,
            'icon' => '',
            'escape' => true,
            'options' => [],
            'params' => [],
            'attributes' => [],
        ];

        $massAction = array_merge($defaults, $massAction);

        $this->massActions[] = new MassAction(
            index: count($this->massActions),
            title: $massAction['title'],
            value: $massAction['value'],
            icon: $massAction['icon'],
            method: $massAction['method'],
            url: $massAction['url'],
            escape: $massAction['escape'],
            options: $massAction['options'],
            params: $massAction['params'],
            attributes: $massAction['attributes']
        );
    }

    /**
     * Render datagrid as view.
     * 
     * @param string $view
     * @return \Illuminate\Contracts\View\View
     */
    public function render(string $view = 'datagrid::datagrid')
    {
        $output = $this->processOutput();

        return view($view, $output);
    }

    /**
     * Render datagrid as json.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderJson()
    {
        return response()->json($this->processOutput());
    }

    /**
     * Transforms datagrid to JSON
     * 
     * @return string
     */
    public function toJson(): string
    {
        return addslashes(json_encode($this->processOutput()));
    }

    /**
     * Transforms datagrid to Array
     * 
     * @return array | null
     */
    public function toArray()
    {
        return $this->processOutput();
    }

    /**
     * Transforms datagrid to JSON for AJAX
     * 
     * @return string
     */
    public function toAjax(): string
    {
        return addslashes(json_encode(['lazyLoad' => true, ...$this->processOutput(true)]));
    }

    /**
     * Get grid name.
     * 
     * @return string
     */
    protected function getGridName(): string
    {
        if (!isset($this->gridName)) {
            $this->gridName = (new \ReflectionClass($this))->getShortName();
        }
        return $this->gridName;
    }

    /**
     * Process output.
     * 
     * @param bool $format
     * @return array
     */
    public function processOutput(bool $format = false)
    {
        if ($this->output !== null) {
            return $this->output;
        }

        $this->init();
        $this->processRequest($format);

        $this->output = [
            'csrf_token' => csrf_token(),
            'uid' => $this->getUid(),
            'baseUrl' => $this->getBaseUrl(),
            'columns' => $this->getColumnsArray(),
            'actions' => $this->getActionsArray(),
            'massActions' => $this->getMassActionsArray(),
            'massActionTitle' => $this->getMassActionTitle(),
            'data' => $this->requestData,
        ];

        return $this->output;
    }

    /**
     * Object DataGrid initialization.
     */
    protected function init()
    {
        $methods = ['prepareItems', 'prepareColumns', 'prepareActions', 'prepareMassActions'];
        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                app()->call([$this, $method]);
            }
        }

        // Check if data source is set
        if (!$this->dataSource) {
            throw new \Exception($this->getGridName() . ' data source is not set');
        }

        // Check if columns are set
        if (empty($this->columns)) {
            throw new \Exception($this->getGridName() . ' columns are not set');
        }

        // Set default sort column
        $this->sortColumn = $this->sortColumn ?? $this->primaryKey;
    }

    /**
     * Validated request.
     */
    public function validatedRequest(): array
    {
        request()->validate([
            'search' => ['sometimes', 'required', 'string'],
            'filters' => ['sometimes', 'required', 'array'],
            'sort' => ['sometimes', 'required', 'array'],
            'page'  => ['sometimes', 'required', 'numeric'],
            'limit' => ['sometimes', 'required', 'numeric'],
            'export' => ['sometimes', 'required', 'string'],
        ]);

        return request()->only(['search', 'filters', 'sort', 'page', 'export', 'limit']);
    }

    /**
     * Process request.
     * 
     * @param bool $format
     * @return void
     */
    public function processRequest(bool $format = false): void
    {
        // Skip processing if already set
        if ($this->requestData !== null) {
            return;
        }

        $request = $this->validatedRequest();

        $limit = min($request['limit'] ?? $this->itemsPerPage, $this->maxItemsPerPage);
        $search = $request['search'] ?? null;
        $filters = $request['filters'] ?? [];
        $sort = $request['sort'] ?? [];

        // Default values
        $paginator = $items = $total = $start = $end = $links = null;
        $hasPages = $currentPage = $hasMorePages = 0;

        // Query and process only if format is false
        if (!$format) {
            $paginator = $this->search($search)
                ->filters($filters)
                ->sort($sort)
                ->paginate($limit)
                ->appends(request()->query());

            $items = $this->format($paginator->items());
            $total = $paginator->total();
            $hasPages = $paginator->hasPages();
            $currentPage = $paginator->currentPage();
            $start = $paginator->firstItem();
            $end = $paginator->lastItem();
            $links = $paginator->linkCollection()->toArray();
            $hasMorePages = $paginator->hasMorePages();
        }

        $this->requestData = [
            'key' => $this->primaryKey,
            'items' => $items ?? [],
            'total' => $total ?? 0,
            'hasPages' => $hasPages,
            'currentPage' => $currentPage,
            'perPageOptions' => $this->perPageOptions,
            'sort' => $this->sortColumn,
            'order' => $this->sortOrder,
            'search' => $search,
            'hasSearch' => $this->searchEnabled,
            'filters' => $filters,
            'start' => $start ?? 0,
            'end' => $end ?? 0,
            'limit' => $paginator ? $paginator->perPage() : $limit,
            'maxItemsPerPageLimit' => $this->maxItemsPerPage,
            'links' => $links ?? [],
            'hasMorePages' => $hasMorePages,
            'requestQuery' => request()->query(),
            'emptyText' => $this->emptyText,
            'loadingText' => $this->loadingText,
        ];
    }

    /**
     * Search.
     * 
     * @param string|null $search
     * @return self
     */
    public function search(?string $search): self
    {
        if (!$search || !$this->searchEnabled) {
            return $this;
        }

        $this->dataSource->search($search, $this->columns);

        return $this;
    }

    /**
     * Filters.
     * 
     * @param array|null $filters
     * @return self
     */
    public function filters(?array $filters): self
    {
        if (!$filters) {
            return $this;
        }

        $this->dataSource->filters($filters, $this->columns);

        return $this;
    }

    /**
     * Sort.
     * 
     * @param array|null $sort
     * @return self
     */
    public function sort(?array $sort): self
    {
        if (!$sort && !$this->defaultSort) {
            return $this;
        }

        $column = $sort['column'] ?? $this->sortColumn;
        $order = $sort['order'] ?? $this->sortOrder;

        if (isset($this->columns[$column]) && $this->columns[$column]->isSortable()) {
            $column = $this->columns[$column]->getColumn();
        } else {
            $column = $this->sortColumn;
        }

        $this->sortColumn = $column;
        $this->sortOrder = $order;

        $this->dataSource->sort([$this->sortColumn], [$this->sortOrder]);

        return $this;
    }

    /**
     * Paginate.
     * 
     * @param int $limit
     * @return mixed
     */
    public function paginate($limit)
    {
        return $this->dataSource->paginate($limit);
    }

    /**
     * Format data.
     * 
     * @param mixed $data
     * @return mixed
     */
    public function format($data)
    {
        $formatterColumns = collect($this->columns)->filter->isFormatter();
        $formatterActions = collect($this->actions);

        if ($formatterColumns->isEmpty() && $formatterActions->isEmpty()) {
            return $data;
        }

        return collect($data)->map(function ($item) use ($formatterColumns, $formatterActions) {
            $formatted = is_array($item) ? $item : $item->toArray();
            // Format columns
            if (!$formatterColumns->isEmpty()) {
                foreach ($formatterColumns as $column) {
                    $formatted[$column->getColumn()] = $column->getFormatter()($item, $column);
                }
            }
            // Add actions
            $formatted['actions'] = $formatterActions->map(fn($action) => $this->formatAction($item, $action))->all();

            return $formatted;
        });
    }

    /**
     * Format action.
     * 
     * @param mixed $item
     * @param Action $action
     * 
     * @return array
     */
    protected function formatAction($item, Action $action): array
    {
        $actionArray = $action->toArray();

        if ($action->isCallableUrl()) {
            $actionArray['url'] = $action->getUrl()($item, $action, $actionArray);
        }
        if ($action->isFormatter()) {
            $actionArray['formatter'] = $action->getFormatter()($item, $action, $actionArray);
        }

        return $actionArray;
    }
}
