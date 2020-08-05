<?php

namespace Mediconesystems\LivewireDatatables\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class DummyBelongsToManyModel extends Model
{

    protected $guarded = ['id'];

    public function dummy_model()
    {
        return $this->belongsToMany(DummyModel::class);
    }
}
