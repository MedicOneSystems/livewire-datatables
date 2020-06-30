<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Exception;
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
        return view('livewire-datatables::boolean', ['value' => $value]);
    }

    public function makeLink($value, $row, $model, $pad = null)
    {
        return view('livewire-datatables::link', [
            'href' => "/$model/$value",
            'slot' => $pad ? str_pad($value, $pad, '0', STR_PAD_LEFT) : $value
        ]);
    }

    public function truncate($value, $row, $length = 16)
    {
        return view('livewire-datatables::tooltip', ['slot' => $value, 'length' => $length]);
    }

    public function highlight($value, $row, $string)
    {
        $output = substr($value, stripos($value, $string), strlen($string));

        return str_ireplace($string, view('livewire::datatables.highlight', ['slot' => $output]), $value);
    }
}
