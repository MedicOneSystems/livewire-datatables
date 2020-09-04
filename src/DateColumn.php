<?php

namespace Mediconesystems\LivewireDatatables;

use Illuminate\Support\Carbon;

class DateColumn extends Column
{
    public $type = 'date';
    public $callback;

    public function __construct()
    {
        $this->format();
    }

    public function format($format = null)
    {
        $this->callback = function ($value) use ($format) {
            return $value ? Carbon::parse($value)->format($format ?? config('livewire-datatables.default_date_format')) : null;
        };

        return $this;
    }
}
