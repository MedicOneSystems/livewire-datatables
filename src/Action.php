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
        $action = new static;
        $action->value = $value;

        return $action;
    }

    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    public static function group($group, $actions)
    {
        if ($actions instanceof \Closure) {
            return collect($actions())->each(function ($item) use ($group) {
                $item->group = $group;
            });
        }
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
