<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Column
{
    public $label;
    public $field;
    public $raw;
    public $searchable;
    public $sort;
    public $defaultSort;
    public $callback;
    public $selectFilter;
    public $booleanFilter;
    public $textFilter;
    public $numberFilter;
    public $dateFilter;
    public $timeFilter;
    public $hidden;
    public $params = [];
    public $additionalSelects = [];

    public static function field($field)
    {
        $column = new static;
        $column->field = $field;
        $column->label = (string) Str::of($field)->after('.')->ucfirst();

        return $column;
    }

    public static function fromRaw($raw)
    {
        $column = new static;
        $column->raw = $raw;
        $column->label = (string) Str::of($raw)->afterLast(' AS ')->replace('`', '');
        $column->sort = DB::raw((string) Str::of($raw)->beforeLast(' AS '));

        return $column;
    }

    public static function scope($scope, $alias)
    {
        $column = new static;
        $column->scope = $scope;
        $column->label = $alias;

        return $column;
    }

    public function label($label)
    {
        $this->label = $label;
        return $this;
    }

    public function sortBy($column)
    {
        $this->sort = $column;
        return $this;
    }

    public function defaultSort($direction = 'desc')
    {
        $this->defaultSort = $direction;
        return $this;
    }

    public function searchable()
    {
        $this->searchable = true;
        return $this;
    }

    public function withSelectFilter($selectFilter)
    {
        $this->selectFilter = $selectFilter;
        return $this;
    }

    public function withScopeSelectFilter($filterScope, $options)
    {
        $this->filterScope = $filterScope;
        $this->selectFilter = $options;
        return $this;
    }

    public function withBooleanFilter()
    {
        $this->booleanFilter = true;
        return $this;
    }

    public function withScopeBooleanFilter($filterScope)
    {
        $this->filterScope = $filterScope;
        return $this;
    }

    public function withTextFilter()
    {
        $this->textFilter = true;
        return $this;
    }

    public function withNumberFilter()
    {
        $this->numberFilter = true;
        return $this;
    }

    public function withDateFilter()
    {
        $this->dateFilter = true;
        return $this;
    }

    public function withTimeFilter()
    {
        $this->timeFilter = true;
        return $this;
    }

    public function formatBoolean()
    {
        $this->callback = 'boolean';
        return $this;
    }

    public function linkTo($model, $pad)
    {
        $this->callback = 'makeLink';
        $this->params = func_get_args();
        return $this;
    }

    public function truncate($length = 16)
    {
        $this->callback = 'truncate';
        $this->params = [$length];
        return $this;
    }

    public function formatDate($format = null)
    {
        $this->callback = 'formatDate';
        $this->params = func_get_args();
        return $this;
    }

    public function formatTime($format = null)
    {
        $this->callback = 'formatTime';
        $this->params = func_get_args();
        return $this;
    }

    public function round($precision = 0)
    {
        $this->callback = 'round';
        $this->params = func_get_args();
        return $this;
    }

    public function callback($callback, $params = [])
    {
        $this->callback = $callback;
        $this->params = $params;

        return $this;
    }

    public function view($view)
    {
        $this->callback = 'view';
        $this->params = func_get_args();

        return $this;
    }

    public function additionalSelects($selects)
    {
        $this->additionalSelects = $selects;

        return $this;
    }

    public function editable()
    {
        if ($this->column) {
            [$table, $column] = explode('.', $this->column);
            $this->additionalSelects[] = $table . '.id AS ' . $table . '.id';
            $this->callback = 'edit';
            $this->params = [$table, $column];
        }
        return $this;
    }

    public function hidden()
    {
        $this->hidden = true;
        return $this;
    }

    public function toggleHidden()
    {
        $this->hidden = !$this->hidden();
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
