<?php

namespace Mediconesystems\LivewireDatatables;

class NumberColumn extends Column
{
    public $type = 'number';
    public $align = 'right';
    public $round;

    public function round($places = 0)
    {
        $this->round = $places;

        $this->callback = function ($value) {
            return round($value, $this->round);
        };

        return $this;
    }
}
