<?php

namespace Mediconesystems\LivewireDatatables;

use phpDocumentor\Reflection\Types\Self_;

class ActionsColumn extends Column
{
    public $type = 'actions';
    public $callback;
    public $buttons;
    public $routes = [];



    public function with($buttons)
    {
        $this->buttons = $buttons;
        return $this;
    }

    public static function actions()
    {
        return static::name('actions as actions_attribute');
    }


    public function viewRoute($route_name, $params = null)
    {
        $this->routes['view'] = [$route_name, $params];
    }

    public function editRoute($route_name, $params = null)
    {
        $this->routes['edit'] = [$route_name, $params];
    }

    public function deleteRoute($route_name, $params = null)
    {
        $this->routes['delete'] = [$route_name, $params];
    }
}
