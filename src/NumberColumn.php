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
    
    // formats & rounds a number with grouped thousands - 1000000 => 1,000,000.00 (If places set to 2)
    public function format($places = 2)
    {

        $this->callback = function ($value) use ($places) {
            return number_format($value, $places, '.', ',');
        };

        return $this;
    }
}
