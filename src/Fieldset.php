<?php

namespace Mediconesystems\LivewireDatatables;

use Mediconesystems\LivewireDatatables\Field;

class Fieldset
{
    public $table;
    public $fields;

    public static function fromModel($model)
    {
        $instance = $model::first();
        $fieldset = new static;
        $fieldset->table = $instance->getTable();

        $fieldset->fields = collect($instance->getAttributes())->keys()->reject(function ($name) use ($instance) {
            return in_array($name, $instance->getHidden());
        })->map(function ($attribute) use ($fieldset) {
            return Field::fromColumn($fieldset->table . '.' . $attribute);
        });

        return $fieldset;
    }

    public function except($fields)
    {
        $fields = is_array($fields) ? $fields : explode(', ', $fields);

        foreach ($fields as  $field) {
            $this->fields = $this->fields->reject(function ($f) use ($field) {
                return $f->column === $field;
            });
        }
        return $this;
    }

    public function formatDates($columns, $format = 'd/m/Y')
    {
        foreach ($columns as $column) {
            if ($field = $this->fields->firstWhere('column', $column)) {
                $field->callback = 'formatDate';
                $field->params = $format;
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

    public function rename($values)
    {
        foreach ($values as $column => $newName) {
            $this->fields->firstWhere('column', $column)->name = $newName;
        }
        return $this;
    }

    public function fields()
    {
        return collect($this->fields);
    }
}
