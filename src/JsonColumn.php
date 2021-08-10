<?php

namespace Mediconesystems\LivewireDatatables;

class JsonColumn extends Column
{
    public $type = 'json';
    public $callback;

    public function __construct()
    {
        $this->callback = function ($value) {
            return $value ? join(', ', json_decode($value)) : null;
        };

        return $this;
    }
}
