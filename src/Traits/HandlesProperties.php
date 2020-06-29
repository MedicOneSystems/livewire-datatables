<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use ReflectionMethod;

trait HandlesProperties
{
    public function processProperties($properties)
    {
        collect((new ReflectionMethod(static::class, 'mount'))->getParameters())->mapWithKeys(function ($p) use ($properties) {
            return [$p->name => $properties[$p->getPosition()]];
        })->each(function ($value, $name) {
            if (in_array($name, ['showHide', 'header', 'paginationControls']) && $value) {
                $this->{$name} = $value;
            } else if (in_array($name, ['except']) && $value) {
                $this->fields = collect($this->fields)->reject(function ($field) use ($value) {
                    return in_array($field['column'], $value);
                })->toArray();
            } else if (in_array($name, ['hidden']) && $value) {
                $this->fields = collect($this->fields)->map(function ($field) use ($value) {
                    $field['hidden'] = in_array($field['column'], $value);
                    return $field;
                })->toArray();
            } else if (in_array($name, ['defaultSort']) && $value) {;
                $this->fields = collect($this->fields)->map(function ($field) use ($value) {
                    if ($field['column'] === key($value)) {
                        $field['defaultSort'] = reset($value);
                    }
                    return $field;
                })->toArray();
            }
        });
    }

    public function addExcepts($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->reject(function ($field) use ($fields) {
            return in_array($field['column'], $fields);
        })->toArray();
    }

    public function addUppercases($fields)
    {
        if (!count($fields)) {
            return;
        }
        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['name'] = strtoupper($field['name']);
            }
            return $field;
        })->toArray();
    }

    public function addTruncates($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['callback'] = 'truncate';
            }
            return $field;
        })->toArray();
    }

    public function addFormatDates($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['callback'] = 'formatDate';
            }
            return $field;
        })->toArray();
    }

    public function addFormatTimes($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['callback'] = 'formatTime';
            }
            return $field;
        })->toArray();
    }

    public function addDateFilters($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['dateFilter'] = true;
            }
            return $field;
        })->toArray();
    }

    public function addTimeFilters($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], $fields)) {
                $field['timeFilter'] = true;
            }
            return $field;
        })->toArray();
    }

    public function addRenames($fields)
    {
        if (!count($fields)) {
            return;
        }

        $this->fields = collect($this->fields)->map(function ($field) use ($fields) {
            if (in_array($field['column'], collect($fields)->keys()->toArray())) {
                $field['name'] = $fields[$field['column']];
            }
            return $field;
        })->toArray();
    }
}
