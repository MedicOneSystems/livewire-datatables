<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Livewire\Livewire;
use Livewire\LivewireManager;
use Mediconesystems\LivewireDatatables\Tests\TestCase;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;
use Mediconesystems\LivewireDatatables\Tests\Classes\DummyTable;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LivewireDatatableTest extends TestCase
{
    /** @test */
    public function it_can_mount_using_properties()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, ['model' => DummyModel::class]);

        $this->assertEquals('Mediconesystems\LivewireDatatables\Tests\Models\DummyModel', $subject->model);
        $this->assertIsArray($subject->fields);
        $this->assertEquals([
            0 => 'Id',
            1 => 'Relation_id',
            2 => 'Subject',
            3 => 'Category',
            4 => 'Body',
            5 => 'Flag',
            6 => 'Expires_at',
            7 => 'Created_at',
            8 => 'Updated_at'
        ], collect($subject->fields)->map->name->toArray());
    }

    /** @test */
    public function it_can_mount_using_the_class()
    {
        factory(DummyModel::class)->create([
            'subject' => 'How to sell paper in Scranton PA'
        ]);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('How to sell paper in Scranton PA');

        $this->assertIsArray($subject->fields);
        $this->assertEquals([
            0 => 'ID',
            1 => 'Subject',
            2 => 'Category',
            3 => 'Body',
            4 => 'Flag',
            5 => 'Expiry',
        ], collect($subject->fields)->map->name->toArray());
    }

    /** @test */
    public function it_can_exclude_fields_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'except' => ['dummy_models.relation_id']
        ]);

        $this->assertIsArray($subject->fields);
        $this->assertEquals([
            0 => 'Id',
            2 => 'Subject',
            3 => 'Category',
            4 => 'Body',
            5 => 'Flag',
            6 => 'Expires_at',
            7 => 'Created_at',
            8 => 'Updated_at'
        ], collect($subject->fields)->map->name->toArray());
    }

    /** @test */
    public function it_can_mark_fields_hidden_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'hidden' => ['dummy_models.relation_id', 'dummy_models.created_at']
        ]);

        $this->assertIsArray($subject->fields);
        $this->assertFalse($subject->fields[0]['hidden']);
        $this->assertTrue($subject->fields[1]['hidden']);
        $this->assertFalse($subject->fields[2]['hidden']);
        $this->assertFalse($subject->fields[3]['hidden']);
        $this->assertFalse($subject->fields[4]['hidden']);
        $this->assertFalse($subject->fields[5]['hidden']);
        $this->assertFalse($subject->fields[6]['hidden']);
        $this->assertTrue($subject->fields[7]['hidden']);
        $this->assertFalse($subject->fields[8]['hidden']);
    }

    /** @test */
    public function it_can_make_field_names_uppercase_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'uppercase' => ['dummy_models.id', 'dummy_models.flag']
        ]);

        $this->assertIsArray($subject->fields);
        $this->assertEquals('ID', $subject->fields[0]['name']);
        $this->assertEquals('Relation_id', $subject->fields[1]['name']);
        $this->assertEquals('Subject', $subject->fields[2]['name']);
        $this->assertEquals('Category', $subject->fields[3]['name']);
        $this->assertEquals('Body', $subject->fields[4]['name']);
        $this->assertEquals('FLAG', $subject->fields[5]['name']);
        $this->assertEquals('Expires_at', $subject->fields[6]['name']);
    }

    /** @test */
    public function it_can_marks_fields_for_truncation_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'truncate' => ['dummy_models.body']
        ]);

        $this->assertIsArray($subject->fields);
        $this->assertEquals('truncate', $subject->fields[4]['callback']);
    }

    /** @test */
    public function it_can_mark_fields_for_date_format_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'formatDates' => ['dummy_models.expires_at']
        ]);

        $this->assertIsArray($subject->fields);

        $this->assertEquals('formatDate', $subject->fields[6]['callback']);
    }

    /** @test */
    public function it_can_mark_fields_for_time_format_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'formatTimes' => ['dummy_models.expires_at']
        ]);

        $this->assertIsArray($subject->fields);

        $this->assertEquals('formatTime', $subject->fields[6]['callback']);
    }

    /** @test */
    public function it_can_marks_fields_for_date_filter_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'dateFilters' => ['dummy_models.created_at', 'dummy_models.updated_at']
        ]);

        $this->assertIsArray($subject->fields);

        $this->assertCount(2, collect($subject->fields)->filter->dateFilter);
    }

    /** @test */
    public function it_can_marks_fields_for_time_filter_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'timeFilters' => ['dummy_models.created_at', 'dummy_models.updated_at']
        ]);

        $this->assertIsArray($subject->fields);

        $this->assertCount(2, collect($subject->fields)->filter->timeFilter);
    }

    /** @test */
    public function it_can_rename_fields_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'renames' => ['dummy_models.id' => 'ID']
        ]);

        $this->assertIsArray($subject->fields);

        $this->assertEquals('ID', $subject->fields[0]['name']);
    }

    /** @test */
    public function it_can_set_a_default_sort()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
        ]);

        $this->assertEquals('Mediconesystems\LivewireDatatables\Tests\Models\DummyModel', $subject->model);
        $this->assertIsArray($subject->fields);

        $this->assertEquals(0, $subject->sort);
        $this->assertFalse($subject->direction);
    }

    /** @test */
    public function it_can_set_sort_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'defaultSort' => ['dummy_models.subject' => 'asc']
        ]);

        $this->assertEquals('Mediconesystems\LivewireDatatables\Tests\Models\DummyModel', $subject->model);
        $this->assertIsArray($subject->fields);

        $this->assertEquals(2, $subject->sort);
        $this->assertTrue($subject->direction);
    }

    /** @test */
    public function it_can_hide_a_column()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);

        $subject = Livewire::test(LivewireDatatable::class, ['model' => DummyModel::class])
            ->assertSee('Beet growing for noobs')
            ->call('toggle', 2)
            ->assertDontSee('Beet growing for noobs');
    }

    /** @test */
    public function it_can_show_a_column()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'hidden' => ['dummy_models.subject']
        ])->assertDontSee('Beet growing for noobs')
            ->call('toggle', 2)
            ->assertSee('Beet growing for noobs');
    }

    /** @test */
    public function it_can_order_results()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);
        factory(DummyModel::class)->create(['subject' => 'Advanced beet growing']);

        $subject = new LivewireDatatable(1);
        $subject->model = DummyModel::class;

        $this->assertEquals('Beet growing for noobs', $subject->results->getCollection()->map->getAttributes()[0]['subject']);
        $this->assertEquals('Advanced beet growing', $subject->results->getCollection()->map->getAttributes()[1]['subject']);

        $subject->forgetComputed();
        $subject->sort = 2;
        $subject->direction = true;

        $this->assertEquals('Advanced beet growing', $subject->results->getCollection()->map->getAttributes()[0]['subject']);
        $this->assertEquals('Beet growing for noobs', $subject->results->getCollection()->map->getAttributes()[1]['subject']);
    }

    /** @test */
    public function it_can_filter_results_based_on_text()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);
        factory(DummyModel::class)->create(['subject' => 'Advanced beet growing']);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2 of 2')
            ->call('doTextFilter', 1, 'Advance')
            ->assertSee('Results 1 - 1 of 1');
    }

    /** @test */
    public function it_can_filter_results_based_on_boolean()
    {
        factory(DummyModel::class)->create(['flag' => true]);
        factory(DummyModel::class)->create(['flag' => false]);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2 of 2')
            ->call('doBooleanFilter', 4)
            ->assertSee('Results 1 - 1 of 1');
    }

    /** @test */
    public function it_can_filter_results_based_on_selects()
    {
        factory(DummyModel::class)->create(['category' => 'Schrute']);
        factory(DummyModel::class)->create(['category' => 'Scott']);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2 of 2')
            ->call('doSelectFilter', 2, 'Scott')
            ->assertSee('Results 1 - 1 of 1');
    }
}
