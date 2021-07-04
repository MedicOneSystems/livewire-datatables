<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Livewire\Livewire;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class LivewireDatatableTemplateTest extends TestCase
{
    /** @test */
    public function it_can_mount_from_the_default_template_with_a_model()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, ['model' => DummyModel::class]);

        $this->assertEquals('Mediconesystems\LivewireDatatables\Tests\Models\DummyModel', $subject->model);
        $this->assertIsArray($subject->columns);
        $this->assertEquals([
            0 => 'Id',
            1 => 'Subject',
            2 => 'Category',
            3 => 'Body',
            4 => 'Flag',
            5 => 'Expires at',
            6 => 'Created at',
            7 => 'Updated at',
        ], collect($subject->columns)->map->label->toArray());
    }

    /** @test */
    public function the_header_can_be_hidden_with_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'hideHeader' => true,
        ])->assertDontSeeHtml('<button wire:click.prefetch="sort');
    }

    /** @test */
    public function the_pagination_bar_can_be_hidden_with_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'hidePagination' => true,
        ])->assertDontSeeHtml('<select name="perPage"');
    }

    /** @test */
    public function it_can_set_per_page_with_a_property()
    {
        factory(DummyModel::class, 20)->create();

        Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'perPage' => 20,
        ])->assertSee('Results 1 - 20');
    }

    /** @test */
    public function it_can_include_columns_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'include' => [
                'id',
                'subject',
                'body',
            ],
        ]);

        $this->assertIsArray($subject->columns);
        $this->assertEquals([
            0 => 'Id',
            1 => 'Subject',
            2 => 'Body',
        ], collect($subject->columns)->map->label->toArray());
    }

    /** @test */
    public function it_can_exclude_columns_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'exclude' => ['subject'],
        ]);

        $this->assertIsArray($subject->columns);
        $this->assertEquals([
            0 => 'Id',
            2 => 'Category',
            3 => 'Body',
            4 => 'Flag',
            5 => 'Expires at',
            6 => 'Created at',
            7 => 'Updated at',
        ], collect($subject->columns)->map->label->toArray());
    }

    /** @test */
    public function it_can_hide_columns_from_a_property()
    {
        factory(DummyModel::class)->create(['subject' => 'HIDE-THIS']);

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'hide' => ['subject'],
        ])->assertDontSee('HIDE-THIS');

        $this->assertIsArray($subject->columns);
        $this->assertCount(8, $subject->columns);
    }

    /** @test */
    public function it_can_mark_columns_for_date_format_from_a_property()
    {
        factory(DummyModel::class)->create([
            'expires_at' => '2020-12-31',
            'created_at' => '1978-10-02',
        ]);

        Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'dates' => ['expires_at', 'created_at|jS F Y'],
        ])->assertSee('31/12/2020')
            ->assertSee('2nd October 1978');
    }

    /** @test */
    public function it_can_mark_columns_for_time_format_from_a_property()
    {
        factory(DummyModel::class)->create([
            'expires_at' => '2020-12-31 2:34 PM',
            'created_at' => '1978-10-02 13:45:56',
        ]);

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'times' => ['expires_at', 'created_at|g:i A'],
        ])
            ->assertSee('14:34')
            ->assertSee('1:45 PM');
    }

    /** @test */
    public function it_can_set_sort_from_a_property()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'sort' => 'subject|asc',
        ]);

        $this->assertEquals('Mediconesystems\LivewireDatatables\Tests\Models\DummyModel', $subject->model);
        $this->assertIsArray($subject->columns);

        $this->assertEquals(1, $subject->sort);
        $this->assertTrue($subject->direction);
    }
}
