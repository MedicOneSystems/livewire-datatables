<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\LivewireServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Mediconesystems\LivewireDatatables\LivewireDatatablesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->withFactories(__DIR__.'/database/factories');
        $this->artisan('migrate', ['--database' => 'sqlite'])->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LivewireDatatablesServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function renderBladeString(string $bladeContent): string
    {
        $temporaryDirectory = sys_get_temp_dir();

        if (! in_array($temporaryDirectory, View::getFinder()->getPaths())) {
            View::addLocation(sys_get_temp_dir());
        }

        $tempFilePath = tempnam($temporaryDirectory, 'tests').'.blade.php';

        file_put_contents($tempFilePath, $bladeContent);

        $bladeViewName = Str::before(pathinfo($tempFilePath, PATHINFO_BASENAME), '.blade.php');

        return view($bladeViewName)->render();
    }
}
