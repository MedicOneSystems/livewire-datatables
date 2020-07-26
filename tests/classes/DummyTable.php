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
    public $perPage = 10;
    public $model = DummyModel::class;

    public function columns()
    {
        return [
            NumericColumn::name('id')
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
        ];
    }
}
