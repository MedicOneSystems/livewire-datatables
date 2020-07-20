<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait WithCallbacks
{
    public function edited($value, $table, $column, $rowId)
    {
        DB::table($table)
            ->where('id', $rowId)
            ->update([$column => $value]);

        $this->emit('fieldEdited', $rowId);
    }
}
