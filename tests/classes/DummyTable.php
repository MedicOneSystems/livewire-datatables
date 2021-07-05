<?php

namespace Mediconesystems\LivewireDatatables\Tests\Classes;

use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class DummyTable extends LivewireDatatable
{
    public $perPage = 10;
    public $model = DummyModel::class;

    public function columns()
    {
        return [
            NumberColumn::name('id')
                ->label('ID')
                ->linkTo('dummy_model', 6),

            Column::name('subject')
                ->filterable(),

            Column::name('category')
                ->filterable(['A', 'B', 'C']),

            Column::name('body')
                ->truncate()
                ->filterable(),

            BooleanColumn::name('flag')
                ->filterable(),

            DateColumn::name('expires_at')
                ->label('Expiry')
                ->format('jS F Y')
                ->hide(),

            Column::name('dummy_has_one.name')
                ->label('Relation'),

            Column::name('subject AS string')
                    ->label('BooleanFilterableSubject')
                    ->booleanFilterable()
                    ->hide(),
        ];
    }
}
