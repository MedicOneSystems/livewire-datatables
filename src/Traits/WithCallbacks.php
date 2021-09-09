<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait WithCallbacks
{
    public function edited($value, $key, $column, $rowId)
    {
        DB::table(Str::before($key, '.'))
            ->where(Str::after($key, '.'), $rowId)
            ->update([$column => $value]);

        $this->emit('fieldEdited', $rowId);
    }
}
