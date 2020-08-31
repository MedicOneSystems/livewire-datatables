<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithPresetTimeFilters
{
    public function nineToFive()
    {
        $this->times['start'] = '09:00:00';
        $this->times['end'] = '17:00:00';
    }

    public function sevenToSevenDay()
    {
        $this->times['start'] = '07:00:00';
        $this->times['end'] = '19:00:00';
    }

    public function sevenToSevenNight()
    {
        $this->times['start'] = '19:00:00';
        $this->times['end'] = '07:00:00';
    }

    public function graveyardShift()
    {
        $this->times['start'] = '22:00:00';
        $this->times['end'] = '06:00:00';
    }
}
