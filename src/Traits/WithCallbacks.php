<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
