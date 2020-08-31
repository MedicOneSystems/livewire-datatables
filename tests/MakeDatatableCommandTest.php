<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Mediconesystems\LivewireDatatables\Commands\MakeDatatableCommand;
use Mediconesystems\LivewireDatatables\Tests\LivewireTestCase as TestCase;

class MakeDatatableCommandTest extends TestCase
{
    /** @test */
    public function component_is_created_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'foo']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
    }

    /** @test */
    public function dot_nested_component_is_created_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'foo.bar']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
    }

    /** @test */
    public function forward_slash_nested_component_is_created_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'foo/bar']);

        $this->assertTrue(File::exists($this->livewireClassesPath('Foo/Bar.php')));
    }

    /** @test */
    public function multiword_component_is_created_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'foo-bar']);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar.php')));
    }

    /** @test */
    public function pascal_case_component_is_automatically_converted_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'FooBar.FooBar']);

        $this->assertTrue(File::exists($this->livewireClassesPath('FooBar/FooBar.php')));
    }

    /** @test */
    public function snake_case_component_is_automatically_converted_by_make_command()
    {
        Artisan::call('livewire:datatable', ['name' => 'text_replace']);

        $this->assertTrue(File::exists($this->livewireClassesPath('TextReplace.php')));
    }

    /** @test */
    public function snake_case_component_is_automatically_converted_by_make_command_on_nested_component()
    {
        Artisan::call('livewire:datatable', ['name' => 'TextManager.text_replace']);

        $this->assertTrue(File::exists($this->livewireClassesPath('TextManager/TextReplace.php')));
    }

    /** @test */
    public function new_component_model_name_matches_option()
    {
        Artisan::call(MakeDatatableCommand::class, ['name' => 'foo', '--model' => 'bar']);

        $this->assertStringContainsString(
            'public $model = Bar::class;',
            File::get($this->livewireClassesPath('Foo.php'))
        );
    }

    /** @test */
    public function a_component_is_not_created_with_a_reserved_class_name()
    {
        Artisan::call('livewire:datatable', ['name' => 'component']);

        $this->assertFalse(File::exists($this->livewireClassesPath('Component.php')));
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Http/Livewire'.($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return resource_path('views').'/livewire'.($path ? '/'.$path : '');
    }
}
