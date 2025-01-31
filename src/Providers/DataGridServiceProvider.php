<?php

declare(strict_types=1);

namespace Zk\DataGrid\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class DataGridServiceProvider extends ServiceProvider
{
    private const BASE_PATH = __DIR__ . '/..';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        // Register package config.
        $this->registerConfig();

        // Load views.
        $this->loadViewsFrom(self::BASE_PATH . '/Resources/views', 'datagrid');

        // Load Blade components.
        Blade::anonymousComponentPath(self::BASE_PATH . '/Resources/views/components', 'datagrid');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void {}

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        if ($this->app->runningInConsole()) {
            // Publish JS and CSS files
            $this->publishes([
                self::BASE_PATH . '/Resources/assets/js/datagrid.min.js' => public_path('js/datagrid.min.js'),
                self::BASE_PATH . '/Resources/assets/css/datagrid.min.css' => public_path('css/datagrid.min.css'),
            ], ['zk-datagrid', 'zk-datagrid-assets']);
        }
    }
}
