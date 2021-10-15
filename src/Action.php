<?php

namespace Mediconesystems\LivewireDatatables;

class Action
{
    public $value;
    public $label;
    public $group;
    public $fileName;
    public $isExport = false;
    public $styles = [];
    public $widths = [];
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

    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    public static function groupBy($group, $actions)
    {
        if ($actions instanceof \Closure) {
            return collect($actions())->each(function ($item) use ($group) {
                $item->group = $group;
            });
        }
    }

    public function export($fileName)
    {
        $this->fileName = $fileName;
        $this->isExport();

        return $this;
    }

    public function isExport($isExport = true)
    {
        $this->isExport = $isExport;

        return $this;
    }

    public function styles($styles)
    {
        $this->styles = $styles;

        return $this;
    }

    public function widths($widths)
    {
        $this->widths = $widths;

        return $this;
    }

    public function callback($callable)
    {
        $this->callable = $callable;

        return $this;
    }
}
