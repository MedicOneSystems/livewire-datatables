<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Column
{
    public $type = 'string';
    public $label;
    public $field;
    public $raw;
    public $searchable;
    public $filterable;
    public $sort;
    public $defaultSort;
    public $callback;
    public $hidden;
    public $scope;
    public $scopeFilter;
    public $params = [];
    public $additionalSelects = [];

    public static function field($field)
    {
        $column = new static;
        $column->field = $field;
        $column->label = (string) Str::of($field)->after('.')->ucfirst();

        return $column;
    }

    public static function raw($raw)
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
        $column->sortBy($alias);

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

    public function filterable($options = null, $scopeFilter = null)
    {
        $this->filterable = $options ?? true;
        $this->scopeFilter = $scopeFilter;
        return $this;
    }

    public function linkTo($model, $pad = 6)
    {
        $this->callback = function($value) use ($model, $pad) {
            return view('datatables::link', [
                'href' => "/$model/$value",
                'slot' => $pad ? str_pad($value, $pad, '0', STR_PAD_LEFT) : $value
            ]);
        };

        return $this;
    }

    public function truncate($length = 16)
    {
        $this->callback = function($value) use ($length) {
            return view('datatables::tooltip', ['slot' => $value, 'length' => $length]);
        };
        return $this;
    }

    public function round($precision = 0)
    {
        $this->callback = function ($value) use ($precision) {
            return $value ? round($value, $precision) : null;
        };
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
        $this->callback = function ($value, $row) use ($view) {
            return view($view, ['value' => $value, 'row' => $row]);
        };

        return $this;
    }

    public function additionalSelects($selects)
    {
        $this->additionalSelects = $selects;

        return $this;
    }

    public function editable()
    {
        if ($this->field) {
            [$table, $column] = explode('.', $this->field);
            $this->additionalSelects[] = $table . '.id AS ' . $table . '.id';

            $this->callback = function ($value, $row) use ($table, $column) {
                return view('datatables::editable', ['value' => $value, 'table' => $table, 'column' => $column, 'rowId' => $row["$table.id"]]);
            };
        }
        return $this;
    }

    public function hide()
    {
        $this->hidden = true;
        return $this;
    }

    public function toggleHidden()
    {
        $this->hidden = !$this->hidden;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
