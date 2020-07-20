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

    public static function fromModelInstance($model)
    {
        return new static(collect($model->getAttributes())->keys()->reject(function ($name) use ($model) {
            return in_array($name, $model->getHidden());
        })->map(function ($attribute) use ($model) {
            return Column::field($model->getTable() . '.' . $attribute);
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
        $include = is_array($include) ? $include : array_map('trim', explode(',', $include));

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

        $exclude = is_array($exclude) ? $exclude : array_map('trim', explode(',', $exclude));

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

        $hidden = is_array($hidden) ? $hidden : array_map('trim', explode(',', $hidden));
        $this->columns->each(function ($column) use ($hidden) {
            $column->hidden = in_array(Str::after($column->field, '.'), $hidden);
        });

        return $this;
    }

    public function formatDates($dates)
    {
        $dates = is_array($dates) ? $dates : array_map('trim', explode(',', $dates));

        $this->columns = $this->columns->map(function ($column) use ($dates) {
            foreach ($dates as $date) {
                if (Str::after($column->field, '.') === Str::before($date, '|')) {
                    $format = Str::of($date)->contains('|') ? Str::after($date, '|') : null;

                    return DateColumn::field($column->field)->format($format);
                }
            }
            return $column;
        });

        return $this;
    }

    public function formatTimes($times)
    {
        $times = is_array($times) ? $times : array_map('trim', explode(',', $times));

        $this->columns = $this->columns->map(function ($column) use ($times) {
            foreach ($times as $time) {
                if (Str::after($column->field, '.') === Str::before($time, '|')) {
                    $format = Str::of($time)->contains('|') ? Str::after($time, '|') : null;
                    return TimeColumn::field($column->field)->format($format);
                }
            }
            return $column;
        });

        return $this;
    }

    public function rename($names)
    {
        if (!$names) {
            return $this;
        }

        $names = is_array($names) ? $names : array_map('trim', explode(',', $names));
        foreach ($names as $name) {
            $this->columns->first(function ($column) use ($name) {
                return Str::after($column->field, '.') === Str::before($name, '|');
            })->label = Str::after($name, '|');
        }
        return $this;
    }

    public function search($searchable)
    {
        if (!$searchable) {
            return $this;
        }

        $searchable = is_array($searchable) ? $searchable : array_map('trim', explode(',', $searchable));
        $this->columns->each(function ($column) use ($searchable) {
            $column->searchable = in_array(Str::after($column->field, '.'), $searchable);
        });

        return $this;
    }

    public function sort($sort)
    {
        if ($sort && $column = $this->columns->first(function ($column) use ($sort) {
            return Str::after($column->field, '.') === Str::before($sort, '|');
        })) {
            $column->defaultSort(Str::of($sort)->contains('|') ? Str::after($sort, '|') : null);
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
