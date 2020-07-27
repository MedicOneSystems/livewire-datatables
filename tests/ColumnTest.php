<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Tests\TestCase;

class ColumnTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_column_from_a_table_column()
    {
        $subject = Column::name('table.column');

        $this->assertEquals('table.column', $subject->name);
        $this->assertEquals('Column', $subject->label);
    }

    /** @test */
    public function it_can_generate_a_column_from_a_scope()
    {
        $subject = Column::scope('fakeScope', 'Alias');

        $this->assertEquals('fakeScope', $subject->scope);
        $this->assertEquals('Alias', $subject->label);
    }

    /**
     * @test
     * @dataProvider settersDataProvider
     */
    public function it_sets_properties_and_parameters($method, $value, $attribute)
    {
        $subject = Column::name('table.column')->$method($value);

        $this->assertEquals($value, $subject->$attribute);
    }

    public function settersDataProvider()
    {
        return [
            ['label', 'Bob Vance', 'label'],
            ['searchable', true, 'searchable'],
            ['filterable', ['Michael Scott', 'Dwight Shrute'], 'filterable'],
            ['hide', true, 'hidden'],
            ['additionalSelects', ['hello world'], 'additionalSelects'],
        ];
    }

    // /**
    //  * @test
    //  * @dataProvider presetCallbacksDataProvider
    //  */
    // public function it_sets_preset_callbacks($method, $value, $attribute)
    // {
    //     $subject = DateColumn::name('table.column')->$method(...$value);

    //     $this->assertEquals($value, $subject->$attribute);
    // }

    // public function presetCallbacksDataProvider()
    // {
    //     return [
    //         ['linkTo', ['model', 'pad'], 'params'],
    //         ['format', ['d/m/Y'], 'params'],
    //         ['round', [2], 'params'],
    //         ['truncate', [2], 'params'],
    //     ];
    // }

    /** @test */
    public function it_returns_an_array_from_column()
    {
        $subject = Column::name('table.column')
            ->label('Column')
            ->filterable(['A', 'B', 'C'])
            ->hide()
            ->linkTo('model', 8)
            ->toArray();

        $this->assertEquals([
            'type' => 'string',
            'name' => 'table.column',
            'base' => null,
            'label' => 'Column',
            'filterable' => ['A', 'B', 'C'],
            'hidden' => true,
            'callback' => function() {},
            'raw' => null,
            'sort' => null,
            'defaultSort' => null,
            'searchable' => null,
            'params' => [],
            'additionalSelects' => [],
            'scope' => null,
            'scopeFilter' => null
        ], $subject);
    }

    /** @test */
    public function it_returns_an_array_from_raw()
    {
        $subject = DateColumn::raw('SELECT column FROM table AS table_column')
            ->filterable()
            ->defaultSort('asc')
            ->format('yyy-mm-dd')
            ->toArray();

        $this->assertEquals([
            'type' => 'date',
            'name' => 'table_column',
            'base' => null,
            'label' => 'table_column',
            'filterable' => true,
            'hidden' => null,
            'callback' => function () {},
            'raw' => 'SELECT column FROM table AS table_column',
            'sort' => DB::raw('SELECT column FROM table'),
            'defaultSort' => 'asc',
            'searchable' => null,
            'params' => [],
            'additionalSelects' => [],
            'scope' => null,
            'scopeFilter' => null
        ], $subject);
    }
}
