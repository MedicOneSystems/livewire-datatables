<?php

namespace Mediconesystems\LivewireDatatables\Tests\Classes;

use Mediconesystems\LivewireDatatables\Field;
use Mediconesystems\LivewireDatatables\Fieldset;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class DummyTable extends LivewireDatatable
{
    public function builder()
    {
        return DummyModel::query();
    }

    public function fieldset()
    {
        return Fieldset::fromArray([
            Field::fromColumn('dummy_models.id')
                ->name('ID')
                ->linkTo('dummy_model', 6),

            Field::fromColumn('dummy_models.subject')
                ->withTextFilter(),

            Field::fromColumn('dummy_models.category')
                ->withSelectFilter(['A', 'B', 'C']),

            Field::fromColumn('dummy_models.body')
                ->truncate()
                ->withTextFilter(),

            Field::fromColumn('dummy_models.flag')
                ->withBooleanFilter()
                ->formatBoolean(),

            Field::fromColumn('dummy_models.expires_at')
                ->name('Expiry')
                ->formatDate('jS F Y')
                ->hidden(),
        ]);
    }
}
