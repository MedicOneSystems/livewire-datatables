<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

trait WithCallbacks
{
    public function formatTime($time, $row, $format = null)
    {
        return $time ? Carbon::parse($time)->format($format ?? config('livewire-datatables.default_time_format')) : null;
    }

    public function formatDate($date, $row, $format = null)
    {
        return $date ? Carbon::parse($date)->format($format ?? config('livewire-datatables.default_date_format')) : null;
    }

    public function round($value, $row, $precision = 0)
    {
        return $value ? round($value, $precision) : null;
    }

    public function boolean($value)
    {
        return $value
            ? 'check-circle'
            : 'x-circle';
    }

    public function makeLink($value, $row, $model, $pad = null)
    {
        return view('livewire-datatables::link', [
            'href' => "/$model/$value",
            'slot' => $pad ? str_pad($value, $pad, '0', STR_PAD_LEFT) : $value
        ]);
    }

    public function truncate($value)
    {
        return view('livewire-datatables::tooltip', ['slot' => $value]);
    }
}
