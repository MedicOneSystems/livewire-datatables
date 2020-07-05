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
        factory(DummyModel::class)->create();

        $subject = ColumnSet::fromModel(DummyModel::class);

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
        factory(DummyModel::class)->create();

        $subject = ColumnSet::fromModel(DummyModel::class)->columns();

        $this->assertEquals($name, $subject[$index]->label);
        $this->assertEquals($column, $subject[$index]->field);
        $this->assertNull($subject[$index]->callback);
        $this->assertNull($subject[$index]->filterable);
        $this->assertNull($subject[$index]->scope);
        $this->assertNull($subject[$index]->scopeFilter);
        $this->assertNull($subject[$index]->hidden);
    }

    public function fieldDataProvider()
    {
        return [
            ['Id', 0, 'dummy_models.id'],
            ['Relation_id', 1, 'dummy_models.relation_id'],
            ['Subject', 2, 'dummy_models.subject'],
            ['Category', 3, 'dummy_models.category'],
            ['Body', 4, 'dummy_models.body'],
            ['Flag', 5, 'dummy_models.flag'],
            ['Expires_at', 6, 'dummy_models.expires_at'],
            ['Created_at', 7, 'dummy_models.created_at'],
            ['Updated_at', 8, 'dummy_models.updated_at'],
        ];
    }

    /** @test */
    public function it_can_exclude_columns()
    {
        factory(DummyModel::class)->create();

        $subject = ColumnSet::fromModel(DummyModel::class)
            ->exclude(['id', 'body'])
            ->columns();

        $this->assertCount(7, $subject);

        $this->assertArrayNotHasKey(0, $subject);
        $this->assertArrayNotHasKey(4, $subject);
    }

    /** @test */
    public function it_can_rename_columns_from_the_model()
    {
        factory(DummyModel::class)->create();

        $subject = ColumnSet::fromModel(DummyModel::class)
            ->rename(['id|ID'])
            ->columns();

        $this->assertEquals('ID', $subject[0]->label);
    }
}
