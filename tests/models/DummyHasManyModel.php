<?php

namespace Mediconesystems\LivewireDatatables\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class DummyHasManyModel extends Model
{
    protected $guarded = ['id'];

    public function dummy_model()
    {
        return $this->belongsTo(DummyModel::class);
    }
}
