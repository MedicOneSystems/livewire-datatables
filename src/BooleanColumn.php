<?php

namespace Mediconesystems\LivewireDatatables;

class BooleanColumn extends Column
{
    public $type = 'boolean';
    public $callback;

    public function __construct()
    {
        $this->callback = function ($value) {
            return view('datatables::boolean', ['value' => $value]);
        };
    }
}
