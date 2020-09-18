<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Livewire\Livewire;
use Mediconesystems\LivewireDatatables\Tests\Classes\DummyTable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class LivewireDatatableExportTest extends TestCase
{
    /** @test */
    public function it_can_mount_using_the_class()
    {
        factory(DummyModel::class)->create([
            'subject' => 'How to sell paper in Scranton PA',
        ]);

        $subject = Livewire::test(DummyTable::class)
            ->assertSee('How to sell paper in Scranton PA')
            ->call('export')
            ->assertSee('How to sell paper in Scranton PA');
    }
}
