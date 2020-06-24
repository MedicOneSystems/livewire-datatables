<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LivewireDatatablesServiceProvider extends ServiceProvider
{
    public function boot()
    {
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
                __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-datatables'),
                __DIR__ . '/../resources/views/icons' => resource_path('views/vendor/livewire-datatables/icons'),
            ], 'views');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'livewire-datatables');
    }
}
