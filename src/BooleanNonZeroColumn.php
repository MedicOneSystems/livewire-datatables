<?php

namespace Mediconesystems\LivewireDatatables;

class BooleanNonZeroColumn extends Column
{
    public $type = 'boolean-non-zero';
    public $callback;

    public function __construct()
    {
        $this->callback = function ($value) {
            return view('datatables::boolean-non-zero', ['value' => $value]);
        };

        $this->exportCallback = function ($value) {
            return filled($value) ? 1 : 0;
        };
    }
}
