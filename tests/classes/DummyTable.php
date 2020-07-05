<?php

namespace Mediconesystems\LivewireDatatables\Tests\Classes;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\NumericColumn;
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
            NumericColumn::field('dummy_models.id')
                ->label('ID')
                ->linkTo('dummy_model', 6),

            Column::field('dummy_models.subject')
                ->filterable(),

            Column::field('dummy_models.category')
                ->filterable(['A', 'B', 'C']),

            Column::field('dummy_models.body')
                ->truncate()
                ->filterable(),

            BooleanColumn::field('dummy_models.flag')
                ->filterable(),

            DateColumn::field('dummy_models.expires_at')
                ->label('Expiry')
                ->format('jS F Y')
                ->hide(),
        ]);
    }
}
