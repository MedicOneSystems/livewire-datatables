<?php

namespace Mediconesystems\LivewireDatatables;


class DateColumn extends Column
{
    public $type = 'date';
    public $callback = 'format';

    public function __construct()
    {
        $this->params = [config('livewire-datatables.default_date_format')];
    }

    public function format($format)
    {
        $this->params = [$format];
        return $this;
    }
}
