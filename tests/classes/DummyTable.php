<?php

namespace Mediconesystems\LivewireDatatables\Tests\Classes;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class DummyTable extends LivewireDatatable
{
    public function builder()
    {
        return DummyModel::query();
    }

    public function columns()
    {
        return ColumnSet::fromArray([
            Column::field('dummy_models.id')
                ->label('ID')
                ->linkTo('dummy_model', 6),

            Column::field('dummy_models.subject')
                ->withTextFilter(),

            Column::field('dummy_models.category')
                ->withSelectFilter(['A', 'B', 'C']),

            Column::field('dummy_models.body')
                ->truncate()
                ->withTextFilter(),

            Column::field('dummy_models.flag')
                ->withBooleanFilter()
                ->formatBoolean(),

            Column::field('dummy_models.expires_at')
                ->label('Expiry')
                ->formatDate('jS F Y')
                ->hide(),
        ]);
    }
}
