<?php

namespace Mediconesystems\LivewireDatatables;

class Action
{
    public $value;
    public $label;
    public $group;
    public $exportable = false;
    public $exportableOptions = [];
    public $callable;

    public function __call($method, $args)
    {
        if (is_callable([$this, $method])) {
            return call_user_func_array($this->$method, $args);
        }
    }

    public static function value($value)
    {
        $column = new static;
        $column->value = $value;

        return $column;
    }

    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    public function exportable($exportable = true, $exportableOptions = [])
    {
        $this->exportable = $exportable;
        $this->exportableOptions = $exportableOptions;

        return $this;
    }

    public function callback($callable)
    {
        $this->callable = $callable;

        return $this;
    }
}
