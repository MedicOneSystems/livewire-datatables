<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Mediconesystems\LivewireDatatables\Column;

class ColumnSet
{
    public $columns;

    public function __construct(Collection $columns)
    {
        $this->columns = $columns;
    }

    public static function fromModel($model)
    {
        $instance = $model::firstOrFail();

        return new static(collect($instance->getAttributes())->keys()->reject(function ($name) use ($instance) {
            return in_array($name, $instance->getHidden());
        })->map(function ($attribute) use ($instance) {
            return Column::field($instance->getTable() . '.' . $attribute);
        }));
    }

    public static function fromArray($columns)
    {
        return new static(collect($columns));
    }

    public function include($include)
    {
        if (!$include) {
            return $this;
        }
        $include = is_array($include) ? $include : explode(', ', $include);

        $this->columns = $this->columns->filter(function ($column) use ($include) {
            return in_array(Str::after($column->field, '.'), $include);
        });

        return $this;
    }

    public function exclude($exclude)
    {
        if (!$exclude) {
            return $this;
        }

        $exclude = is_array($exclude) ? $exclude : explode(', ', $exclude);

        $this->columns = $this->columns->reject(function ($column) use ($exclude) {
            return in_array(Str::after($column->field, '.'), $exclude);
        });

        return $this;
    }

    public function hide($hidden)
    {
        if (!$hidden) {
            return $this;
        }

        $hidden = is_array($hidden) ? $hidden : explode(', ', $hidden);
        $this->columns->each(function ($column) use ($hidden) {
            $column->hidden = in_array(Str::after($column->field, '.'), $hidden);
        });

        return $this;
    }

    public function formatDates($dates)
    {
        $dates = is_array($dates) ? $dates : explode(', ', $dates);

        foreach ($dates as $date) {
            if ($column = $this->columns->first(function ($column) use ($date) {
                return Str::after($column->field, '.') === Str::before($date, '|');
            })) {
                $column->callback = 'format';
                $column->params = Str::of($date)->contains('|') ? [Str::after($date, '|')] : [config('livewire-datatables.default_date_format')];
            }
        }
        return $this;
    }

    public function formatTimes($times)
    {
        $times = is_array($times) ? $times : explode(', ', $times);

        foreach ($times as $time) {

            if ($column = $this->columns->first(function ($column) use ($time) {
                return Str::after($column->field, '.') === Str::before($time, '|');
            })) {
                $column->callback = 'format';
                $column->params = Str::of($time)->contains('|') ? [Str::after($time, '|')] : [config('livewire-datatables.default_time_format')];
            }
        }
        return $this;
    }

    public function rename($names)
    {
        $names = is_array($names) ? $names : explode(', ', $names);

        foreach ($names as $name) {
            $this->columns->first(function ($column) use ($name) {
                return Str::after($column->field, '.') === Str::before($name, '|');
            })->label = Str::after($name, '|');
        }
        return $this;
    }

    public function search($search)
    {
        if (!$search) {
            return $this;
        }

        $search = is_array($search) ? $search : explode(', ', $search);

        $this->columns->each(function ($column) use ($search) {
            $column->globalSearch = in_array(Str::after($column->field, '.'), $search);
        });

        return $this;
    }

    public function sort($sort)
    {
        if ($sort) {
            $this->columns->first(function ($column) use ($sort) {
                return Str::after($column->field, '.') === Str::before($sort, '|');
            })->defaultSort = Str::after($sort, '|');
        }
        return $this;
    }

    public function columns()
    {
        return collect($this->columns);
    }

    public function columnsArray()
    {
        return $this->columns()->map->toArray()->toArray();
    }
}
