<?php

namespace Mediconesystems\LivewireDatatables\Traits;

trait WithPresetDateFilters
{
    public function lastMonth()
    {
        $this->dates['start'] = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->dates['end'] = now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function lastQuarter()
    {
        $this->dates['start'] = now()->subQuarter()->startOfQuarter()->format('Y-m-d');
        $this->dates['end'] = now()->subQuarter()->endOfQuarter()->format('Y-m-d');
    }

    public function lastYear()
    {
        $this->dates['start'] = now()->subYear()->startOfYear()->format('Y-m-d');
        $this->dates['end'] = now()->subYear()->endOfYear()->format('Y-m-d');
    }

    public function monthToToday()
    {
        $this->dates['start'] = now()->subMonth()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }

    public function quarterToToday()
    {
        $this->dates['start'] = now()->subQuarter()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }

    public function yearToToday()
    {
        $this->dates['start'] = now()->subYear()->addDay()->format('Y-m-d');
        $this->dates['end'] = now()->format('Y-m-d');
    }
}
