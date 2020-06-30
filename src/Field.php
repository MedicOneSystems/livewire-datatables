<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Field
{
    public $name;
    public $column;
    public $raw;
    public $globalSearch;
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


    public static function fromColumn($column)
    {
        $field = new static;
        $field->column = $column;
        $field->name = (string) Str::of($column)->after('.')->ucfirst();

        return $field;
    }

    public static function fromRaw($raw)
    {
        $field = new static;
        $field->raw = $raw;
        $field->name = (string) Str::of($raw)->afterLast(' AS ')->replace('`', '');
        $field->sort = DB::raw((string) Str::of($raw)->beforeLast(' AS '));

        return $field;
    }

    public static function fromScope($scope, $alias)
    {
        $field = new static;
        $field->scope = $scope;
        $field->name = $alias;

        return $field;
    }

    public function name($name)
    {
        $this->name = $name;
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

    public function globalSearch()
    {
        $this->globalSearch = true;
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

    public function withNumberFilter($range)
    {
        $this->numberFilter = $range;
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
