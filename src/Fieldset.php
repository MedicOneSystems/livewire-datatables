<?php

namespace Mediconesystems\LivewireDatatables;

use Mediconesystems\LivewireDatatables\Field;

class Fieldset
{
    public $fields;

    public function __construct($fields)
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

    public function except($fields)
    {
        $fields = is_array($fields) ? $fields : explode(', ', $fields);

        $this->fields = $this->fields->reject(function ($f) use ($fields) {
            return in_array($f->column, $fields);
        });

        return $this;
    }

    public function hidden($fields)
    {
        $fields = is_array($fields) ? $fields : explode(', ', $fields);

        foreach ($fields as $field) {
            if ($field = $this->fields->firstWhere('column', $field)) {
                $field->hidden = true;
            }
        }

        return $this;
    }

    public function formatDates($columns, $format = 'd/m/Y')
    {
        foreach ($columns as $column) {
            if ($field = $this->fields->firstWhere('column', $column)) {
                $field->callback = 'formatDate';
                $field->params = [$format];
            }
        }
        return $this;
    }

    public function dateFilters($columns)
    {
        foreach ($columns as $column) {
            if ($field = $this->fields->firstWhere('column', $column)) {
                $field->dateFilter = true;
            }
        }
        return $this;
    }

    public function uppercase($values)
    {
        foreach ($values as $column) {
            $field = $this->fields->firstWhere('column', $column);
            $field->name = strtoupper($field->name);
        }
        return $this;
    }

    public function rename($values)
    {
        foreach ($values as $column => $newName) {
            $this->fields->firstWhere('column', $column)->name = $newName;
        }
        return $this;
    }

    public function truncate($values)
    {
        foreach ($values as $column) {
            $field = $this->fields->firstWhere('column', $column);
            $field->callback = 'truncate';
            $field->params = func_get_args();
        };
        return $this;
    }

    public function fields()
    {
        return collect($this->fields);
    }
}
