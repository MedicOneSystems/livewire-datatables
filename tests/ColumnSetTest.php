<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\ColumnSet;
use Mediconesystems\LivewireDatatables\Tests\TestCase;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class ColumnSetTest extends TestCase
{
    /** @test */
    public function it_can_generate_an_array_of_columns_from_a_model()
    {
        $model = factory(DummyModel::class)->create();

        $subject = ColumnSet::build($model);

        $this->assertCount(9, $subject->columns());

        $subject->columns()->each(function ($column) {
            $this->assertIsObject($column, Column::class);
        });
    }

    /**
     * @test
     * @dataProvider fieldDataProvider
     */
    public function it_can_correctly_populate_the_columns_from_the_model($name, $index, $column)
    {
        $model = factory(DummyModel::class)->create();

        $subject = ColumnSet::build($model)->columns();

        $this->assertEquals($name, $subject[$index]->label);
        $this->assertEquals($column, $subject[$index]->name);
        $this->assertNull($subject[$index]->callback);
        $this->assertNull($subject[$index]->filterable);
        $this->assertNull($subject[$index]->scope);
        $this->assertNull($subject[$index]->scopeFilter);
        $this->assertNull($subject[$index]->hidden);
    }

    public function fieldDataProvider()
    {
        return [
            ['Relation_id', 0, 'relation_id'],
            ['Subject', 1, 'subject'],
            ['Category', 2, 'category'],
            ['Body', 3, 'body'],
            ['Flag', 4, 'flag'],
            ['Expires_at', 5, 'expires_at'],
            ['Updated_at', 6, 'updated_at'],
            ['Created_at', 7, 'created_at'],
            ['Id', 8, 'id'],
        ];
    }

    /** @test */
    public function it_can_exclude_columns()
    {
        $model = factory(DummyModel::class)->create();

        $subject = ColumnSet::build($model)
            ->exclude(['id', 'body'])
            ->columns();

        $this->assertCount(7, $subject);

        $this->assertArrayNotHasKey(8, $subject);
        $this->assertArrayNotHasKey(3, $subject);
    }

    /** @test */
    public function it_can_rename_columns_from_the_model()
    {
        $model = factory(DummyModel::class)->create();

        $subject = ColumnSet::build($model)
            ->rename(['id|ID'])
            ->columns();

        $this->assertEquals('ID', $subject[8]->label);
    }
}
