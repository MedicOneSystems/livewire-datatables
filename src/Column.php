<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Column
{
    public $type = 'string';
    public $label;
    public $name;
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

    public static function name($name)
    {
        $column = new static;
        $column->name = $name;
        $column->label = (string) Str::of($name)->after('.')->ucfirst();


        if (Str::contains(Str::lower($name), ' as ')) {
            $column->name = array_reverse(preg_split("/ as /i", $name))[0];
            $column->base = preg_split("/ as /i", $name)[0];
        }

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

    public static function callback($name, $callback, $params = [])
    {
        $column = new static;
        $column->name = "callback_$name";
        $column->callback = $callback;
        $column->params = $params;

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

    public function linkTo($model, $pad = null)
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

    public function view($view)
    {
        $this->callback = function ($value, $row) use ($view) {
            return view($view, ['value' => $value, 'row' => $row]);
        };

        return $this;
    }

    public function additionalSelects($selects)
    {
        $selects = is_array($selects) ? $selects : array_map('trim', explode(',', $selects));

        $this->additionalSelects = $selects;

        return $this;
    }

    public function editable()
    {
        // if ($this->name) {
        //     [$table, $column] = explode('.', $this->name);
        //     $this->additionalSelects[] = $table . '.id AS ' . $table . '.id';

        //     $this->callback = function ($value, $row) use ($table, $column) {
        //         return view('datatables::editable', ['value' => $value, 'table' => $table, 'column' => $column, 'rowId' => $row->{"$table.id"}]);
        //     };
        // }
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
