<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Field
{
    public $name;
    public $column;
    public $callback;
    public $selectFilter;
    public $booleanFilter;
    public $textFilter;
    public $dateFilter;
    public $timeFilter;
    public $hidden;


    public static function fromColumn($column)
    {
        $field = new static;
        $field->column = $column;
        $field->name = (string) Str::of($column)->after('.')->ucfirst();

        return $field;
    }

    public static function fromScope($scope, $alias)
    {
        $field = new static;
        $field->scope = $scope;
        $field->name = $alias;

        return $field;
    }

    public static function fromDynamicScope($scope)
    {
        $field = new static;
        $field->dynamicScope = $scope;
        $field->name = 'mmmmmm';

        return $field;
    }

    public function name($name)
    {
        $this->name = $name;
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

    public function linkTo($model)
    {
        $this->callback = 'makeLink';
        $this->params = $model;
        return $this;
    }

    public function formatDate($format = 'd/m/Y')
    {
        $this->callback = 'formatDate';
        $this->params = $format;
        return $this;
    }

    public function formatTime($format = 'H:i')
    {
        $this->callback = 'formatTime';
        $this->params = $format;
        return $this;
    }

    public function round($precision = 0)
    {
        $this->callback = 'round';
        $this->params = $precision;
        return $this;
    }

    public function callback($callback, $params = null)
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

    public function toArray()
    {
        return get_object_vars($this);
    }
}
