<?php

namespace Mediconesystems\LivewireDatatables\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class DummyHasOneModel extends Model
{
    protected $guarded = ['id'];

    public function dummy_models()
    {
        return $this->belongsTo(DummyModel::class);
    }
}
