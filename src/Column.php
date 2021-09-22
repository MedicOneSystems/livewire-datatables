<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Column
{
    public $type = 'string';
    public $label;
    public $name;
    public $select;
    public $joins;
    public $base;
    public $raw;
    public $searchable;
    public $filterOn;
    public $filterable;
    public $hideable;
    public $sort;
    public $unsortable;
    public $defaultSort;
    public $callback;
    public $hidden;
    public $scope;
    public $scopeFilter;
    public $params = [];
    public $additionalSelects = [];
    public $filterView;
    public $align = 'left';
    public $preventExport;
    public $width;
    public $exportCallback;

    /**
     * @var string (optional) you can group your columns to let the user toggle the visibility of a group at once.
     */
    public $group;

    /** @var array list all column types that are not sortable by SQL here */
    public const UNSORTABLE_TYPES = ['label', 'checkbox'];

    public static function name($name)
    {
        $column = new static;
        $column->name = $name;
        $column->aggregate = Str::contains($name, ':') ? Str::after($name, ':') : $column->aggregate();
        $column->label = (string) Str::of($name)->after('.')->ucfirst()->replace('_', ' ');

        if (Str::contains(Str::lower($name), ' as ')) {
            $column->name = array_reverse(preg_split('/ as /i', $name))[0];
            $column->label = array_reverse(preg_split('/ as /i', $name))[1];
            $column->base = preg_split('/ as /i', $name)[0];
        }

        return $column;
    }

    public static function raw($raw)
    {
        $column = new static;
        $column->raw = $raw;
        $column->name = Str::after($raw, ' AS ');
        $column->select = DB::raw(Str::before($raw, ' AS '));
        $column->label = (string) Str::of($raw)->afterLast(' AS ')->replace('`', '');
        $column->sort = (string) Str::of($raw)->beforeLast(' AS ');

        return $column;
    }

    public static function callback($columns, $callback, $params = [])
    {
        $column = new static;

        $column->name = 'callback_' . crc32(json_encode(func_get_args()));
        $column->callback = $callback;
        $column->additionalSelects = is_array($columns) ? $columns : array_map('trim', explode(',', $columns));
        $column->params = $params;

        return $column;
    }

    public static function checkbox($attribute = 'id')
    {
        return static::name($attribute . ' as checkbox_attribute')
            ->setType('checkbox')
            ->excludeFromExport();
    }

    public static function scope($scope, $alias)
    {
        $column = new static;
        $column->scope = $scope;
        $column->name = $alias;
        $column->label = $alias;
        $column->sortBy("`$alias`");

        return $column;
    }

    public static function delete($name = 'id')
    {
        return static::callback($name, function ($value) {
            return view('datatables::delete', ['value' => $value]);
        });
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

    public function defaultSort($direction = true)
    {
        $this->defaultSort = $direction;

        return $this;
    }

    public function hideable()
    {
        $this->hideable = true;

        return $this;
    }

    public function unsortable()
    {
        $this->unsortable = true;

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

    public function filterOn($query)
    {
        $this->filterOn = $query;

        return $this;
    }

    public function booleanFilterable()
    {
        $this->filterable = true;
        $this->filterView = 'boolean';

        return $this;
    }

    public function exportCallback($exportCallback)
    {
        $this->exportCallback = $exportCallback;

        return $this;
    }

    public function excludeFromExport()
    {
        $this->preventExport = true;

        return $this;
    }

    public function linkTo($model, $pad = null)
    {
        $this->callback = function ($value) use ($model, $pad) {
            return view('datatables::link', [
                'href' => url("/$model/$value"),
                'slot' => $pad ? str_pad($value, $pad, '0', STR_PAD_LEFT) : $value,
            ]);
        };

        $this->exportCallback = function ($value) {
            return $value;
        };

        return $this;
    }

    public function link($href, $slot = null)
    {
        $this->callback = function ($caption, $row) use ($href, $slot) {
            $substitutes = ['{{caption}}' => $caption];

            foreach ($row as $attribute => $value) {
                $substitutes["{{{$attribute}}}"] = $value;
            }

            if (! $slot) {
                $slot = $caption;
            }

            return view('datatables::link', [
                'href' => strtr($href, $substitutes),
                'slot' => strtr($slot, $substitutes),
            ]);
        };

        $this->exportCallback = function ($value) {
            return $value;
        };

        return $this;
    }

    public function truncate($length = 16)
    {
        $this->callback = function ($value) use ($length) {
            return view('datatables::tooltip', ['slot' => $value, 'length' => $length]);
        };

        $this->exportCallback = function ($value) {
            return $value;
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

        $this->exportCallback = function ($value) {
            return $value;
        };

        return $this;
    }

    public function filterView($view)
    {
        $this->filterView = $view;

        return $this;
    }

    public function additionalSelects($selects)
    {
        $this->additionalSelects = is_array($selects) ? $selects : array_map('trim', explode(',', $selects));

        return $this;
    }

    public function editable($editable = true)
    {
        $this->exportCallback = function ($value) {
            return $value;
        };

        return $editable ? $this->setType('editable') : $this;
    }

    public function isEditable()
    {
        return $this->type === 'editable';
    }

    public function hide()
    {
        $this->hidden = true;

        return $this;
    }

    public function alignRight()
    {
        $this->align = 'right';

        return $this;
    }

    public function alignCenter()
    {
        $this->align = 'center';

        return $this;
    }

    public function toggleHidden()
    {
        $this->hidden = ! $this->hidden;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function aggregate()
    {
        return $this->type === 'string'
            ? 'group_concat'
            : 'count';
    }

    public function isBaseColumn()
    {
        return ! Str::contains($this->name, '.') && ! $this->raw;
    }

    public function field()
    {
        return Str::afterLast($this->name, '.');
    }

    /**
     * You can use group(null) to revoke a column from a group, if necessary.
     *
     * @see array LivewireDatatable->groupLabels to assign a human readable and translatable label for the group
     */
    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    public function relations()
    {
        return $this->isBaseColumn() ? null : collect(explode('.', Str::beforeLast($this->name, '.')));
    }

    public function isType($type)
    {
        return $type === $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function addParams($params)
    {
        $this->params = $params;

        return $this;
    }

    public function width($width)
    {
        if (preg_match('/^\\d*\\.?\\d+$/i', $width) === 1) {
            $width .= 'px';
        }

        if (preg_match('/^(\\d*\\.?\\d+)\\s?(cm|mm|in|px|pt|pc|em|ex|ch|rem|vw|vmin|vmax|%+)$/i', $width) === 0) {
            return $this;
        }

        $this->width = $width;

        return $this;
    }
}
