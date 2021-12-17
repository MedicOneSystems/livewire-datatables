<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class PutSortToSessionTest extends TestCase
{
    /** @test */
    public function it_saves_sort_to_session_when_not_in_multisort()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
        ]);

        $subject->call('sort', 1);
        $this->assertSession(1, ['1|desc'], $sortSessionKey = Str::snake(Str::afterLast(LivewireDatatable::class, '\\')) . '_sort');

        $subject->call('sort', 1);
        $this->assertSession(1, ['1|asc'], $sortSessionKey);

        $subject->call('sort', 0);
        $this->assertSession(1, ['0|desc'], $sortSessionKey);
    }

    /** @test */
    public function it_saves_sort_to_session_when_in_multisort()
    {
        factory(DummyModel::class)->create();

        $subject = Livewire::test(LivewireDatatable::class, [
            'model' => DummyModel::class,
            'multisort' => true
        ]);

        $subject->call('sort', 1);
        $this->assertSession(2, ['0|desc', '1|desc'], $multisortSessionKey = Str::snake(Str::afterLast(LivewireDatatable::class, '\\')) . '_multisort');

        $subject->call('sort', 1);
        $this->assertSession(2, ['0|desc', '1|asc'], $multisortSessionKey);

        $subject->call('sort', 2);
        $this->assertSession(3, ['0|desc', '1|asc', '2|desc'], $multisortSessionKey);
    }

    private function assertSession(int $expectedCount, array $columnsIndexDirection, string $sessionKey)
    {
        $session = session()->get($sessionKey);
        $this->assertCount($expectedCount, $session);
        foreach ($columnsIndexDirection as $columnIndexDirection) {
            $this->assertTrue(in_array($columnIndexDirection, $session));
        }

    }
}
