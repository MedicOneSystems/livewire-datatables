<?php

namespace Mediconesystems\LivewireDatatables\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithCallbacks
{
    public function edited($value, $key, $column, $rowId)
    {
        DB::connection($this->connection())->table(Str::beforeLast($key, '.'))
            ->where(Str::afterLast($key, '.'), $rowId)
            ->update([$column => $value]);

        $this->emit('fieldEdited', $rowId);
    }

    public abstract function connection();
}
