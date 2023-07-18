<?php

namespace Mediconesystems\LivewireDatatables;

class JsonColumn extends Column
{
    public $callback;
    public $type = 'json';
    public $filterView = 'string';

    public function __construct()
    {
        $this->callback = function ($value) {
            return $value ? join(', ', json_decode($value)) : null;
        };

        return $this;
    }
}
