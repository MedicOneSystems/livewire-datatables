<?php

namespace Mediconesystems\LivewireDatatables\Commands;

use Illuminate\Support\Facades\File;
use Livewire\Commands\FileManipulationCommand;
use Livewire\LivewireComponentsFinder;

class MakeDatatableCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:datatable {name} {--model=}';

    protected $desciption = 'Create a new Livewire Datatable';

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            $this->argument('name'),
            $this->option('model')
        );

        if ($this->isReservedClassName($name = $this->parser->className())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name}");

            return;
        }

        $class = $this->createClass();

        $this->refreshComponentAutodiscovery();

        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->relativeClassPath()}");
    }

    protected function createClass()
    {
        $classPath = $this->parser->classPath();

        if (File::exists($classPath)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($classPath);

        File::put($classPath, $this->parser->classContents());

        return $classPath;
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }

    public function isReservedClassName($name)
    {
        return array_search($name, ['Parent', 'Component', 'Interface']) !== false;
    }
}
