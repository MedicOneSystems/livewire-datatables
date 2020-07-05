<?php

namespace Mediconesystems\LivewireDatatables;


class TimeColumn extends Column
{
    public $type = 'time';
    public $callback = 'format';

    public function __construct()
    {
        $this->params = [config('livewire-datatables.default_time_format')];
    }

    public function format($format)
    {
        $this->params = [$format];
        return $this;
    }
}
