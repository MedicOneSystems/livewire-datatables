<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait WithCallbacks
{
    public function format($value, $row, $format)
    {
        return $value ? Carbon::parse($value)->format($format) : null;
    }

    public function round($value, $row, $precision = 0)
    {
        return $value ? round($value, $precision) : null;
    }

    public function boolean($value)
    {
        return view('datatables::boolean', ['value' => $value]);
    }

    public function makeLink($value, $row, $model, $pad = null)
    {
        return view('datatables::link', [
            'href' => "/$model/$value",
            'slot' => $pad ? str_pad($value, $pad, '0', STR_PAD_LEFT) : $value
        ]);
    }

    public function truncate($value, $row, $length = 16)
    {
        return view('datatables::tooltip', ['slot' => $value, 'length' => $length]);
    }

    public function highlight($value, $row, $string)
    {
        $output = substr($value, stripos($value, $string), strlen($string));

        return str_ireplace($string, view('datatables::highlight', ['slot' => $output]), $value);
    }

    public function view($value, $row, $view)
    {
        return view($view, ['value' => $value, 'row' => $row]);
    }

    public function edit($value, $row, $table, $column)
    {
        return view('datatables::editable', ['value' => $value, 'table' => $table, 'column' => $column, 'rowId' => $row["$table.id"]]);
    }

    public function edited($value, $table, $column, $row)
    {
        DB::table($table)
            ->where('id', $row)
            ->update([$column => $value]);

        $this->emit('fieldEdited', $row);
    }
}
