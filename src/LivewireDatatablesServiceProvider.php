<?php

namespace Mediconesystems\LivewireDatatables;

use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mediconesystems\LivewireDatatables\Tests\Classes\DummyTable;
use Mediconesystems\LivewireDatatables\Commands\MakeDatatableCommand;
use Mediconesystems\LivewireDatatables\Http\Controllers\FileExportController;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class LivewireDatatablesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::component('datatable', LivewireDatatable::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views/livewire/datatables', 'datatables');
        $this->loadViewsFrom(__DIR__ . '/../resources/views/icons', 'icons');

        Blade::component('icons::arrow-left', 'icons.arrow-left');
        Blade::component('icons::arrow-right', 'icons.arrow-right');
        Blade::component('icons::arrow-circle-left', 'icons.arrow-circle-left');
        Blade::component('icons::chevron-up', 'icons.chevron-up');
        Blade::component('icons::chevron-down', 'icons.chevron-down');
        Blade::component('icons::cog', 'icons.cog');
        Blade::component('icons::excel', 'icons.excel');
        Blade::component('icons::x-circle', 'icons.x-circle');
        Blade::component('icons::check-circle', 'icons.check-circle');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/livewire-datatables.php' => config_path('livewire-datatables.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views/livewire/datatables' => resource_path('views/livewire/datatables'),
                __DIR__ . '/../resources/views/icons' => resource_path('views/livewire/datatables/icons'),
            ], 'views');

            $this->commands([MakeDatatableCommand::class]);
        }

        Route::get('/datatables/{filename}', [FileExportController::class, 'handle'])
        ->middleware(config('livewire.middleware_group', 'web'))
        ->name('livewire.preview-file');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livewire-datatables.php', 'livewire-datatables');
    }

    protected function loadViewsFrom($path, $namespace)
    {
        $this->callAfterResolving('view', function ($view) use ($path, $namespace) {
            if (
                isset($this->app->config['view']['paths']) &&
                is_array($this->app->config['view']['paths'])
            ) {
                foreach ($this->app->config['view']['paths'] as $viewPath) {
                    if (is_dir($appPath = $viewPath . '/livewire/' . $namespace)) {
                        $view->addNamespace($namespace, $appPath);
                    }
                }
            }

            $view->addNamespace($namespace, $path);
        });
    }
}
