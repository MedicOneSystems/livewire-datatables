<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Orchestra\Testbench\TestCase;
use Mediconesystems\LivewireDatatables\LivewireDatatablesServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LivewireDatatablesServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
