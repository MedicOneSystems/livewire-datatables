<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mediconesystems\LivewireDatatables\Commands\MakeDatatableCommand;
use Mediconesystems\LivewireDatatables\Http\Controllers\FileExportController;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LivewireDatatablesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::component('datatable', LivewireDatatable::class);

        $this->loadViewsFrom(__DIR__.'/../resources/views/livewire/datatables', 'datatables');
        $this->loadViewsFrom(__DIR__.'/../resources/views/icons', 'icons');

        Blade::component('icons::arrow-left', 'icons.arrow-left');
        Blade::component('icons::arrow-right', 'icons.arrow-right');
        Blade::component('icons::arrow-circle-left', 'icons.arrow-circle-left');
        Blade::component('icons::chevron-up', 'icons.chevron-up');
        Blade::component('icons::chevron-down', 'icons.chevron-down');
        Blade::component('icons::cog', 'icons.cog');
        Blade::component('icons::trash', 'icons.trash');
        Blade::component('icons::excel', 'icons.excel');
        Blade::component('icons::x-circle', 'icons.x-circle');
        Blade::component('icons::check-circle', 'icons.check-circle');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/livewire-datatables.php' => config_path('livewire-datatables.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/views/livewire/datatables' => resource_path('views/livewire/datatables'),
                __DIR__.'/../resources/views/icons' => resource_path('views/livewire/datatables/icons'),
            ], 'views');

            $this->commands([MakeDatatableCommand::class]);
        }

        Route::get('/datatables/{filename}', [FileExportController::class, 'handle'])
            ->middleware(config('livewire.middleware_group', 'web'));

        $this->loadBuilderMacros();
        $this->loadEloquentBuilderMacros();
        $this->loadRelationMacros();
    }

    public function loadBuilderMacros()
    {
        Builder::macro('leftJoinIfNotJoined', function (...$params) {
            $isJoined = collect($this->joins)->pluck('table')->contains($params[0]);

            return $isJoined ? $this : call_user_func_array([$this, 'leftJoin'], $params);
        });

        Builder::macro('groupIfNotGrouped', function (...$params) {
            $isGrouped = collect($this->groups)->contains($params[0]);

            return $isGrouped ? $this : call_user_func_array([$this, 'groupBy'], $params);
        });
    }

    public function loadEloquentBuilderMacros()
    {
        EloquentBuilder::macro('customWithAggregate', function ($relations, $aggregate, $column, $alias = null) {
            if (empty($relations)) {
                return $this;
            }

            $relations = is_array($relations) ? $relations : [$relations];

            foreach ($this->parseWithRelations($relations) as $name => $constraints) {
                $segments = explode(' ', $name);

                if (count($segments) == 3 && Str::lower($segments[1]) == 'as') {
                    [$name, $alias] = [$segments[0], $segments[2]];
                }

                $relation = $this->getRelationWithoutConstraints($name);

                $table = $relation->getRelated()->newQuery()->getQuery()->from === $this->getQuery()->from
                    ? $relation->getRelationCountHashWithoutIncrementing()
                    : $relation->getRelated()->getTable();

                $query = $relation->getRelationExistenceAggregatesQuery(
                    $relation->getRelated()->newQuery(),
                    $this,
                    $aggregate,
                    $table.'.'.($column ?? 'id')
                );

                $query->callScope($constraints);

                $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();
                // dd($relations, $column, $aggregate);
                if (count($query->columns) > 1) {
                    $query->columns = [$query->columns[0]];
                }
                $columnAlias = new Expression('`'.($alias ?? collect([$relations, $column])->filter()->flatten()->join('.')).'`');
                $this->selectSub($query, $columnAlias);
            }
            // $this->groupIfNotGrouped($this->getModel()->getTable() . '.' . $this->getModel()->getKeyName());
            return $this;
        });

        EloquentBuilder::macro('hasAggregate', function ($relation, $column, $aggregate, $operator = '>=', $count = 1) {
            if (is_string($relation)) {
                $relation = $this->getRelationWithoutConstraints($relation);
            }

            $table = $relation->getRelated()->newQuery()->getQuery()->from === $this->getQuery()->from
                ? $relation->getRelationCountHashWithoutIncrementing()
                : $relation->getRelated()->getTable();

            $hasQuery = $relation->getRelationExistenceAggregatesQuery(
                $relation->getRelated()->newQueryWithoutRelationships(),
                $this,
                $aggregate,
                $table.'.'.$column
            );

            $hasQuery->mergeConstraintsFrom($relation->getQuery());

            return $this->addWhereCountQuery($hasQuery->toBase(), $operator, $count, 'and');
        });
    }

    public function loadRelationMacros()
    {
        Relation::macro('getRelationExistenceAggregatesQuery', function (EloquentBuilder $query, EloquentBuilder $parentQuery, $aggregate, $column) {
            $distinct_aggregate = new Expression($aggregate."(distinct {$column} separator ', ')");

            if ($query->getConnection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $distinct_aggregate = new Expression($aggregate."(REPLACE(DISTINCT({$column}), '', '') , ', ')");
            }

            $expression = $aggregate === 'group_concat'
                ? $distinct_aggregate
                : new Expression('COALESCE('.$aggregate."({$column}),0)");

            return $this->getRelationExistenceQuery(
                $query,
                $parentQuery,
                $expression
            )->setBindings([], 'select');
        });

        Relation::macro('getRelationCountHashWithoutIncrementing', function () {
            return 'laravel_reserved_'.static::$selfJoinCount;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/livewire-datatables.php', 'livewire-datatables');
    }

    protected function loadViewsFrom($path, $namespace)
    {
        $this->callAfterResolving('view', function ($view) use ($path, $namespace) {
            if (
                isset($this->app->config['view']['paths']) &&
                is_array($this->app->config['view']['paths'])
            ) {
                foreach ($this->app->config['view']['paths'] as $viewPath) {
                    if (is_dir($appPath = $viewPath.'/livewire/'.$namespace)) {
                        $view->addNamespace($namespace, $appPath);
                    }
                }
            }

            $view->addNamespace($namespace, $path);
        });
    }
}
