<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Livewire\Livewire;
use Mediconesystems\LivewireDatatables\Tests\Classes\DummyTable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class LivewireDatatableClassTest extends TestCase
{
    /** @test */
    public function it_can_mount_using_the_class()
    {
        factory(DummyModel::class)->create([
            'subject' => 'How to sell paper in Scranton PA',
        ]);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('How to sell paper in Scranton PA');

        $this->assertIsArray($subject->columns);
        $this->assertEquals([
            0 => 'ID',
            1 => 'Subject',
            2 => 'Category',
            3 => 'Body',
            4 => 'Flag',
            5 => 'Expiry',
            6 => 'Relation',
            7 => 'BooleanFilterableSubject',
        ], collect($subject->columns)->map->label->toArray());
    }

    /** @test */
    public function it_can_set_a_default_sort()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(DummyTable::class);

        $this->assertIsArray($subject->columns);

        $this->assertEquals(0, $subject->sort);
        $this->assertFalse($subject->direction);
    }

    /** @test */
    public function it_can_show_and_hide_a_column()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Beet growing for noobs')
            ->call('toggle', 1)
            ->assertDontSee('Beet growing for noobs')
            ->call('toggle', 1)
            ->assertSee('Beet growing for noobs')
            ->call('toggle', 1)
            ->assertDontSee('Beet growing for noobs');
    }

    /** @test */
    public function it_can_order_results()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);
        factory(DummyModel::class)->create(['subject' => 'Advanced beet growing']);

        $subject = new DummyTable(1);

        $this->assertEquals('Beet growing for noobs', $subject->results->getCollection()[0]->subject);
        $this->assertEquals('Advanced beet growing', $subject->results->getCollection()[1]->subject);

        $subject->forgetComputed();
        $subject->sort = 1;
        $subject->direction = true;

        $this->assertEquals('Advanced beet growing', $subject->results->getCollection()[0]->subject);
        $this->assertEquals('Beet growing for noobs', $subject->results->getCollection()[1]->subject);
    }

    /** @test */
    public function it_can_filter_results_based_on_text()
    {
        factory(DummyModel::class)->create(['subject' => 'Beet growing for noobs']);
        factory(DummyModel::class)->create(['subject' => 'Advanced beet growing']);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2')
            ->call('doTextFilter', 1, 'Advance')
            ->assertSee('Results 1 - 1');
    }

    /** @test */
    public function it_can_filter_results_based_on_boolean()
    {
        factory(DummyModel::class)->create(['flag' => true]);
        factory(DummyModel::class)->create(['flag' => false]);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2')
            ->call('doBooleanFilter', 4, '1')
            ->assertSee('Results 1 - 1');
    }

    /** @test */
    public function it_can_filter_strings_as_a_boolean()
    {
        factory(DummyModel::class)->create(['subject' => 'has contents']);
        factory(DummyModel::class)->create(['subject' => '']);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2')
            ->call('doBooleanFilter', 7, '1')
            ->assertSee('Results 1 - 1');
    }

    /** @test */
    public function it_can_filter_results_based_on_selects()
    {
        factory(DummyModel::class)->create(['category' => 'Schrute']);
        factory(DummyModel::class)->create(['category' => 'Scott']);
        $subject = Livewire::test(DummyTable::class)
            ->assertSee('Results 1 - 2')
            ->call('doSelectFilter', 2, 'Scott')
            ->assertSee('Results 1 - 1');
    }

    /** @test */
    public function it_can_filter_results_based_on_numbers()
    {
        factory(DummyModel::class)->create(['id' => 1]);
        factory(DummyModel::class)->create(['id' => 2]);
        factory(DummyModel::class)->create(['id' => 3]);
        factory(DummyModel::class)->create(['id' => 4]);
        factory(DummyModel::class)->create(['id' => 5]);

        $subject = Livewire::test(DummyTable::class)
            ->set('columns.0.numberFilter.0.min', 0)
            ->set('columns.0.numberFilter.0.max', 1000000)
            ->assertSee('Results 1 - 5')
            ->call('doNumberFilterStart', 0, 2)
            ->assertSee('Results 1 - 4')
            ->call('doNumberFilterEnd', 0, 3)
            ->assertSee('Results 1 - 2')
            ->call('removeNumberFilter', 0)
            ->assertSee('Results 1 - 5');
    }
}
