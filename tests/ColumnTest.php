<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Tests\TestCase;

class ColumnTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_column_from_a_table_column()
    {
        $subject = Column::field('table.column');

        $this->assertEquals('table.column', $subject->field);
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
        $subject = Column::field('table.column')->$method($value);

        $this->assertEquals($value, $subject->$attribute);
    }

    public function settersDataProvider()
    {
        return [
            ['label', 'Bob Vance', 'label'],
            ['searchable', true, 'searchable'],
            ['withSelectFilter', ['Michael Scott', 'Dwight Shrute'], 'selectFilter'],
            ['withBooleanFilter', true, 'booleanFilter'],
            ['withScopeBooleanFilter', 'scope', 'filterScope'],
            ['withTextFilter', true, 'textFilter'],
            ['withDateFilter', true, 'dateFilter'],
            ['withTimeFilter', true, 'timeFilter'],
            ['formatBoolean', 'boolean', 'callback'],
            ['hide', true, 'hidden'],
            ['additionalSelects', ['hello world'], 'additionalSelects'],
        ];
    }

    /**
     * @test
     * @dataProvider presetCallbacksDataProvider
     */
    public function it_sets_preset_callbacks($method, $value, $attribute)
    {
        $subject = Column::field('table.column')->$method(...$value);

        $this->assertEquals($value, $subject->$attribute);
    }

    public function presetCallbacksDataProvider()
    {
        return [
            ['linkTo', ['model', 'pad'], 'params'],
            ['formatDate', ['d/m/Y'], 'params'],
            ['formatTime', ['H:i'], 'params'],
            ['round', [2], 'params'],
            ['truncate', [2], 'params'],
        ];
    }

    /** @test */
    public function it_returns_an_array_from_column()
    {
        $subject = Column::field('table.column')
            ->label('Column')
            ->withSelectFilter(['A', 'B', 'C'])
            ->hide()
            ->linkTo('model', 8)
            ->toArray();

        $this->assertEquals([
            'field' => 'table.column',
            'label' => 'Column',
            'selectFilter' => ['A', 'B', 'C'],
            'hidden' => true,
            'callback' => 'makeLink',
            'booleanFilter' => null,
            'textFilter' => null,
            'numberFilter' => null,
            'dateFilter' => null,
            'timeFilter' => null,
            'raw' => null,
            'sort' => null,
            'defaultSort' => null,
            'searchable' => null,
            'params' => ['model', 8],
            'additionalSelects' => [],
        ], $subject);
    }

    /** @test */
    public function it_returns_an_array_from_raw()
    {
        $subject = Column::fromRaw('SELECT column FROM table AS table_column')
            ->withBooleanFilter()
            ->defaultSort('asc')
            ->formatDate('yyy-mm-dd')
            ->toArray();

        $this->assertEquals([
            'field' => null,
            'label' => 'table_column',
            'selectFilter' => null,
            'hidden' => null,
            'callback' => 'formatDate',
            'booleanFilter' => true,
            'textFilter' => null,
            'numberFilter' => null,
            'dateFilter' => null,
            'timeFilter' => null,
            'raw' => 'SELECT column FROM table AS table_column',
            'sort' => DB::raw('SELECT column FROM table'),
            'defaultSort' => 'asc',
            'searchable' => null,
            'params' => ['yyy-mm-dd'],
            'additionalSelects' => [],
        ], $subject);
    }
}
