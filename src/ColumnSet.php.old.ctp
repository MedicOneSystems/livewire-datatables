<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            })->map(function ($attribute) use ($model) {
                return Column::name($attribute);
            })
        );
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
        if (!$exclude) {
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
        if (!$hidden) {
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
        if (!$searchable) {
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

    public function processForBuilder($builder)
    {
        $this->columns = $this->columns->map(function ($column) use ($builder) {

            foreach (array_merge([$column->base ?? $column->name], $column->additionalSelects) as $name) {

                if (!Str::contains($name, '.')) {
                    if (!Str::startsWith($name, 'callback_')) {
                        $selects[] = $builder->getModel()->getTable() . '.' . $name;
                        if ($column->isEditable()) {
                            $selects[] = $builder->getModel()->getTable() . '.' . $builder->getModel()->getKeyName() . ' AS ' . $builder->getModel()->getTable() . '.' . $builder->getModel()->getKeyName();
                        }
                    }
                }

                $parent = $builder;
                foreach (explode('.', Str::beforeLast($name, '.')) as $join) {

                    if (method_exists($parent->getModel(), $join)) {
                        $relation = $parent->getRelation($join);
                        // dump($parent, $join, $relation);
                        if ($relation instanceof HasOne || $relation instanceof BelongsTo) {
                            $column->joins[] = [
                                $relation->getRelated()->getTable(),
                                $relation instanceof HasOne ? $relation->getQualifiedForeignKeyName() : $relation->getQualifiedOwnerKeyName(),
                                $relation instanceof HasOne ? $relation->getQualifiedParentKeyName() : $relation->getQualifiedForeignKeyName()
                            ];

                            $parent = $relation;

                            $selects = [$parent->getRelated()->getTable() . '.' . Str::afterLast($name, '.') . ($name === $column->name
                                ? ' AS ' . $name
                                : '')];
                        }

                        if ($relation instanceof HasMany || $relation instanceof BelongsToMany) {
                            $name = explode('.', $name);
                            $column->aggregates[] = [$name[0], $column->aggregate(), $name[1]];
                        }
                    }
                }
            }

            if (count($selects) > 1) {
                if ($column->callback && !$column->isEditable()) {

                    $column->additionalSelects = [];
                    $column->select = DB::raw('CONCAT_WS("' . static::SEPARATOR . '" ,' .
                        collect($selects)->map(function ($select) {
                            return "COALESCE($select, '')";
                        })->join(', ') . ')' . ' AS  `' . $column->name . '`');
                } else {
                    $column->select = array_shift($selects);
                    $column->additionalSelects = $selects;
                }
            } else if (count($selects)) {
                foreach ($selects as $select) {
                    $column->select = $select . ($column->callback ? ' AS  ' . $column->name : '');
                }
            }

            return $column;
        });

        // dd($this->columns);
        return $this;
    }
}
