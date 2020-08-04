<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DatatableQueryBuilder
{
    public $query;
    public $columns;

    public function __construct($query, $columns)
    {
        $this->query = $query;
        $this->columns = $columns;
    }

    public static function make($query, $columns)
    {
        $query->addSelect($columns->map(
            function ($column) use ($query) {

                //callback field
                if (Str::startsWith($column->name, 'callback_')) {
                    $selects = collect($column->additionalSelects)->map(function ($select) use ($column) {
                        return $select . ' AS ' . $column->name;
                    });
                }

                //scope field
                if ($column->scope) {
                    return false;
                }

                // raw field
                if ($column->raw) {
                    return DB::raw($column->raw);
                }

                return $column->name;
            }
        )->flatten()->filter()->map(function ($name) use ($query) {
            return static::getQualifiedColumnName($query, $name, true);
        })

            ->toArray());

        return $query;
    }

    public static function getQualifiedColumnName($query, $name, $withJoins = false)
    {
        if (!Str::contains($name, '.')) {
            return $query->getModel()->getTable() . '.' . $name;
        }

        if (!method_exists($query->getModel(), Str::before($name, '.'))) {
            return $name;
        }

        $parent = $query;
        foreach (explode('.', Str::beforeLast($name, '.')) as $i => $join) {
            $relation = method_exists($parent->getModel(), $join)
                ? $parent->getRelation($join)
                : $parent;

            switch (true) {
                case $relation instanceof HasOne:
                    $table = $relation->getRelated()->getTable();
                    $lk = $relation->getQualifiedForeignKeyName();
                    $fk = $relation->getQualifiedParentKeyName();
                    break;

                case $relation instanceof BelongsTo:
                    $table = $relation->getRelated()->getTable();
                    $lk = $relation->getQualifiedOwnerKeyName();
                    $fk = $relation->getQualifiedForeignKeyName();
                    break;

                case $relation instanceof HasMany:
                    $name = explode('.', $name);
                    $query->withAggregate($name[0], $column->aggregate(), $name[1]);
                    break;

                case $relation instanceof BelongsToMany:
                    $name = explode('.', $name);
                    $query->withAggregate($name[0], $column->aggregate(), $name[1]);
                    break;
            }
            if ($withJoins) {

                $query->leftJoinIfNotJoined($table, $lk, $fk);
            }
            $parent = $relation;
        }

        if ($relation instanceof HasOne || $relation instanceof BelongsTo) {
            $field = Str::afterLast($name, '.');
            return $parent->getRelated()->getTable() . '.' . $field . ' AS ' . $name;
        }
    }
}
