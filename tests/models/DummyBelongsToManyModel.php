<?php

namespace Mediconesystems\LivewireDatatables\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DummyBelongsToManyModel extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function dummy_model()
    {
        return $this->belongsToMany(DummyModel::class);
    }
}
