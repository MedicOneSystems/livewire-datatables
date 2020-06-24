<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Mediconesystems\LivewireDatatables\Field;
use Orchestra\Testbench\TestCase;
use Mediconesystems\LivewireDatatables\LivewireDatatablesServiceProvider;

class FieldTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LivewireDatatablesServiceProvider::class];
    }

    /** @test */
    public function it_can_generate_a_field_from_a_table_column()
    {
        $subject = Field::fromColumn('table.column');

        $this->assertEquals('table.column', $subject->column);
        $this->assertEquals('Column', $subject->name);
    }

    /** @test */
    public function it_can_generate_a_field_from_a_scope()
    {
        $subject = Field::fromScope('fakeScope', 'Alias');

        $this->assertEquals('fakeScope', $subject->scope);
        $this->assertEquals('Alias', $subject->name);
    }

    /**
     * @test
     * @dataProvider settersDataProvider
     */
    public function it_sets_properties_and_parameters($method, $value, $attribute)
    {
        $subject = Field::fromColumn('table.column')
            ->$method($value);

        $this->assertEquals($value, $subject->$attribute);
    }

    public function settersDataProvider()
    {
        return [
            ['name', 'Bob Vance', 'name'],
            ['withSelectFilter', ['Michael Scott', 'Dwight Shrute'], 'selectFilter'],
            ['withBooleanFilter', true, 'booleanFilter'],
            ['withScopeBooleanFilter', 'scope', 'filterScope'],
            ['withTextFilter', true, 'textFilter'],
            ['withDateFilter', true, 'dateFilter'],
            ['withTimeFilter', true, 'timeFilter'],
            ['formatBoolean', 'boolean', 'callback'],
            ['linkTo', 'model', 'params'],
            ['formatDate', 'd/m/Y', 'params'],
            ['formatTime', 'H:i', 'params'],
            ['round', 2, 'params'],
            ['hidden', true, 'hidden'],
        ];
    }

    /** @test */
    public function it_returns_an_array()
    {
        $subject = Field::fromColumn('table.column')
            ->name('Column')
            ->withSelectFilter(['A', 'B', 'C'])
            ->hidden()
            ->toArray();

        $this->assertEquals([
            'column' => 'table.column',
            'name' => 'Column',
            'selectFilter' => ['A', 'B', 'C'],
            'hidden' => true,
            'callback' => null,
            'booleanFilter' => null,
            'textFilter' => null,
            'dateFilter' => null,
            'timeFilter' => null
        ], $subject);
    }
}
