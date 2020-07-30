<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;

class AggregateNumberColumn extends Column
{
    public $type = 'aggregate';
    public $filterView = 'number';
    public $aggregate;
    public $column;

    public static function name($name)
    {
        $column = new static;

        $name = Str::contains($name, ':')
            ? $name
            : $name . ':count';

        $name = Str::contains(Str::before($name, ':'), '.')
            ? $name
            : Str::before($name, ':') .  '.id:' . Str::after($name, ':');

        $column->name = str_replace('.', '_', str_replace(':', '_', $name));

        $column->aggregate = Str::after($name, ':');

        $column->label = (string) Str::of($name)->before(':')->before('.')->ucfirst();

        if (Str::contains(Str::lower($name), ' as ')) {
            $column->name = array_reverse(preg_split("/ as /i", $name))[0];
            $column->base = preg_split("/ as /i", $name)[0];
        }

        return $column;
    }
}
