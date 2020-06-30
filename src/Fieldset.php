<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Mediconesystems\LivewireDatatables\Field;

class Fieldset
{
    public $fields;

    public function __construct(Collection $fields)
    {
        $this->fields = $fields;
    }

    public static function fromModel($model)
    {
        $instance = $model::firstOrFail();

        return new static(collect($instance->getAttributes())->keys()->reject(function ($name) use ($instance) {
            return in_array($name, $instance->getHidden());
        })->map(function ($attribute) use ($instance) {
            return Field::fromColumn($instance->getTable() . '.' . $attribute);
        }));
    }

    public static function fromArray($fields)
    {
        return new static(collect($fields));
    }

    // public function except($fields)
    // {
    //     $fields = is_array($fields) ? $fields : explode(', ', $fields);

    //     $this->fields = $this->fields->reject(function ($f) use ($fields) {
    //         return in_array($f->column, $fields);
    //     });

    //     return $this;
    // }

    public function include($include)
    {
        if (!$include) {
            return $this;
        }
        $include = is_array($include) ? $include : explode(', ', $include);

        $this->fields = $this->fields->filter(function ($field) use ($include) {
            return in_array(Str::after($field->column, '.'), $include);
        });

        return $this;
    }

    public function exclude($exclude)
    {
        if (!$exclude) {
            return $this;
        }

        $exclude = is_array($exclude) ? $exclude : explode(', ', $exclude);

        $this->fields = $this->fields->reject(function ($field) use ($exclude) {
            return in_array(Str::after($field->column, '.'), $exclude);
        });

        return $this;
    }

    public function hidden($hidden)
    {
        if (!$hidden) {
            return $this;
        }

        $hidden = is_array($hidden) ? $hidden : explode(', ', $hidden);

        $this->fields->each(function ($field) use ($hidden) {
            $field->hidden = in_array(Str::after($field->column, '.'), $hidden);
        });

        return $this;
    }

    public function formatDates($dates)
    {
        $dates = is_array($dates) ? $dates : explode(', ', $dates);

        foreach ($dates as $date) {

            if ($field = $this->fields->first(function ($field) use ($date) {
                return Str::after($field->column, '.') === Str::before($date, '|');
            })) {
                $field->callback = 'formatDate';
                $field->params = Str::of($date)->contains('|') ? [Str::after($date, '|')] : [];
            }
        }
        return $this;
    }

    public function formatTimes($times)
    {
        $times = is_array($times) ? $times : explode(', ', $times);

        foreach ($times as $time) {

            if ($field = $this->fields->first(function ($field) use ($time) {
                return Str::after($field->column, '.') === Str::before($time, '|');
            })) {
                $field->callback = 'formatTime';
                $field->params = Str::of($time)->contains('|') ? [Str::after($time, '|')] : [];
            }
        }
        return $this;
    }

    public function rename($names)
    {
        foreach ($names as $name) {
            $this->fields->first(function ($field) use ($name) {
                return Str::after($field->column, '.') === Str::before($name, '|');
            })->name = Str::after($name, '|');
        }
        return $this;
    }

    public function sort($sort)
    {
        if ($sort) {
            $this->fields->first(function ($field) use ($sort) {
                return Str::after($field->column, '.') === Str::before($sort, '|');
            })->defaultSort = Str::after($sort, '|');
        }
        return $this;
    }

    public function fields()
    {
        return collect($this->fields);
    }

    public function fieldsArray()
    {
        return $this->fields()->map->toArray()->toArray();
    }
}
