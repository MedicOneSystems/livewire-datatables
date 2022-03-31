<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ColumnSet
{
    public $columns;

    public function __construct(Collection $columns)
    {
        $this->columns = $columns;
    }

    public static function build($input)
    {
        return is_array($input)
            ? self::fromArray($input)
            : self::fromModelInstance($input);
    }

    public static function fromModelInstance($model)
    {
        return new static(
            collect($model->getAttributes())->keys()->reject(function ($name) use ($model) {
                return in_array($name, $model->getHidden());
            })->map(function ($attribute, $index) {
                return Column::name($attribute)->setIndex($index);
            })
        );
    }

    public static function fromArray($columns)
    {
        return new static(collect(static::squeezeIndex($columns)));
    }

    /**
     * Takes an array of columns and squeezes the consecutive index inside each element.
     */
    public static function squeezeIndex($columns)
    {
        foreach ($columns as $index => $column) {
            $column->setIndex($index);
        }

        return $columns;
    }

    public function include($include)
    {
        if (! $include) {
            return $this;
        }

        $include = collect(is_array($include) ? $include : array_map('trim', explode(',', $include)));
        $this->columns = $include->map(function ($column) {
            return Str::contains($column, '|')
                ? Column::name(Str::before($column, '|'))->label(Str::after($column, '|'))
                : Column::name($column);
        });

        return $this;
    }

    public function exclude($exclude)
    {
        if (! $exclude) {
            return $this;
        }

        $exclude = is_array($exclude) ? $exclude : array_map('trim', explode(',', $exclude));

        $this->columns = $this->columns->reject(function ($column) use ($exclude) {
            return in_array(Str::after($column->name, '.'), $exclude);
        });

        return $this;
    }

    public function hide($hidden)
    {
        if (! $hidden) {
            return $this;
        }
        $hidden = is_array($hidden) ? $hidden : array_map('trim', explode(',', $hidden));
        $this->columns->each(function ($column) use ($hidden) {
            $column->hidden = in_array(Str::after($column->name, '.'), $hidden);
        });

        return $this;
    }

    public function formatDates($dates)
    {
        $dates = is_array($dates) ? $dates : array_map('trim', explode(',', $dates));

        $this->columns = $this->columns->map(function ($column) use ($dates) {
            foreach ($dates as $date) {
                if ($column->name === Str::before($date, '|')) {
                    $format = Str::of($date)->contains('|') ? Str::after($date, '|') : null;

                    return DateColumn::name($column->name)->format($format);
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
                if (Str::after($column->name, '.') === Str::before($time, '|')) {
                    $format = Str::of($time)->contains('|') ? Str::after($time, '|') : null;

                    return TimeColumn::name($column->name)->format($format);
                }
            }

            return $column;
        });

        return $this;
    }

    public function search($searchable)
    {
        if (! $searchable) {
            return $this;
        }

        $searchable = is_array($searchable) ? $searchable : array_map('trim', explode(',', $searchable));
        $this->columns->each(function ($column) use ($searchable) {
            $column->searchable = in_array($column->name, $searchable);
        });

        return $this;
    }

    public function sort($sort)
    {
        if ($sort && $column = $this->columns->first(function ($column) use ($sort) {
            return Str::after($column->name, '.') === Str::before($sort, '|');
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
