<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;

class AggregateStringColumn extends Column
{
    public $type = 'aggregate';
    public $filterView = 'string';
    public $aggregate = 'group_concat';

    public static function name($name)
    {
        $column = new static;

        $column->name = str_replace('.', '_', $name) . '_group_concat';

        $column->label = str_replace('.', ' ', $name);

        if (Str::contains(Str::lower($name), ' as ')) {
            $column->name = array_reverse(preg_split("/ as /i", $name))[0];
            $column->base = preg_split("/ as /i", $name)[0];
        }

        return $column;
    }
}
