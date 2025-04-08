
# ZK DataGrid for Laravel

ZK DataGrid is a powerful and customizable data grid package for Laravel applications. It simplifies data representation with support for sorting, filtering, searching, pagination, and exporting.

---

## ✨ Features

- **Data Grid Management** – Easily create and manage data grids.
- **Customizable Columns** – Define and customize columns with type, formatting, search, and filter options.
- **Actions & Mass Actions** – Add row-level actions and bulk actions.
- **Multiple Data Sources** – Works with arrays, collections, and query builders.
- **Blade Integration** – Predefined Blade templates for easy rendering.
- **Built-in Assets** – Includes CSS & JS for interactivity and styling.
- **Export Support** – Export grid data with customizable formatters.

---

## 📦 Installation

Install via Composer:

```bash
composer require zk/laravel-datagrid
```

---

## ⚙️ Configuration

After installation, publish the config and views:

```bash
php artisan vendor:publish --tag=zk-datagrid
```

---

## 🚀 Usage

### Create a DataGrid Class

**Example: `App\DataGrid\PostGrid.php`**

```php
use Zk\DataGrid\DataGrid;
use App\Models\Post;

class PostGrid extends DataGrid {

    public function prepareItems(): void
    {
        $this->fromQuery(Post::query());
    }

    public function prepareColumns(): void
    {
        // Serial No
        $this->addColumn([
            'title' => '#',
            'column' => '',
            'type' => 'serial-no',
        ]);

        // Title
        $this->addColumn([
            'column' => 'title',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        // Status
        $this->addColumn([
            'column' => 'status',
            'type' => 'number',
            'sortable' => true,
            'formatter' => fn($item) => $item->status ? 'Active' : 'Inactive',
        ]);
    }
}
```

**In Controller:**

```php
$grid = app(\App\DataGrid\PostGrid::class);
return view('post.index', ['grid' => $grid]);
```

---

## 🖥️ Rendering in Blade

```blade
{!! $grid->render() !!}
```

---

## ⏳ Lazy Loading (AJAX)

Update your Blade template:

```blade
<div id="grid-render"></div>

@push('scripts')
    <script src="{{ asset('js/datagrid.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataGridLazy = new ZkDataGrid();
            dataGridLazy.render('#grid-render', "{!! $grid->toAjax() !!}");
        });
    </script>
@endpush
```

---

## 📤 Exporting Data

Export the grid data:

```php
$exportData = $grid->export(); // Params: $all = false, $options = []
```

- All columns are exported by default.
- To exclude a column, add `'export' => false` in its definition.
- To export **all data** (not paginated), pass `true` as the first parameter.
- Use the `export` formatter for custom export logic:

```php
'export' => function ($item, $column, $options) {
    // Return formatted value for export
}
```

---

## 🔍 Searchable and Filterable Columns

### Searchable

```php
'searchable' => true,
```

Custom search query:

```php
'searchable' => function($query, $searchTerm, $column) {
    // Custom query logic
}
```

### Filterable

```php
'filterable' => true,
```

Custom filter query:

```php
'filterable' => function($query, $filterTerm, $column) {
    // Custom filter logic
}
```

Customize filter input:

```php
'filterParams' => [
    'type' => 'select', // Options: select, checkbox, radio
    'options' => [
        ['label' => 'All', 'value' => ''],
        ['label' => 'Active', 'value' => 1],
        ['label' => 'Inactive', 'value' => 0],
    ],
    'attributes' => '', // Custom HTML attributes
]
```

---

## 🤝 Contributing

Contributions are welcome! Feel free to submit issues and pull requests to enhance the package.

---
<!---
## 🪪 License

This package is open-source and licensed under the [MIT License](LICENSE).
--->
