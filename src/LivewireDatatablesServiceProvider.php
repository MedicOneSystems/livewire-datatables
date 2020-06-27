<?php

namespace Mediconesystems\LivewireDatatables;

use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mediconesystems\LivewireDatatables\Http\Livewire\Datatable;

class LivewireDatatablesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::component('datatable', Datatable::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'datatables');
        $this->loadViewsFrom(__DIR__ . '/../resources/views/icons', 'icons');

        Blade::component('icons::arrow-left', 'icons.arrow-left');
        Blade::component('icons::arrow-right', 'icons.arrow-right');
        Blade::component('icons::chevron-up', 'icons.chevron-up');
        Blade::component('icons::chevron-down', 'icons.chevron-down');
        Blade::component('icons::cog', 'icons.cog');
        Blade::component('icons::x-circle', 'icons.x-circle');
        Blade::component('icons::check-circle', 'icons.check-circle');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('livewire-datatables.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views/livewire' => resource_path('views/livewire'),
                __DIR__ . '/../resources/views/icons' => resource_path('views/vendor/livewire-datatables/icons'),
            ], 'views');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livewire-datatables.php', 'livewire-datatables');
    }
}
