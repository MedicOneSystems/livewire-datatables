<?php

namespace Mediconesystems\LivewireDatatables\Commands;

use Illuminate\Support\Str;

class ComponentParser
{
    protected $model;
    protected $appPath;
    protected $viewPath;
    protected $component;
    protected $componentClass;
    protected $directories;

    public function __construct($classNamespace, $rawCommand, $model = null)
    {
        $this->model = $model;

        $this->baseClassNamespace = $classNamespace;

        $classPath = static::generatePathFromNamespace($classNamespace);

        $this->baseClassPath = rtrim($classPath, DIRECTORY_SEPARATOR) . '/';

        $directories = preg_split('/[.\/]+/', $rawCommand);

        $camelCase = Str::camel(array_pop($directories));
        $kebabCase = Str::kebab($camelCase);

        $this->component = $kebabCase;
        $this->componentClass = Str::studly($this->component);

        $this->directories = array_map([Str::class, 'studly'], $directories);
    }

    public function component()
    {
        return $this->component;
    }

    public function classPath()
    {
        return $this->baseClassPath . collect()
            ->concat($this->directories)
            ->push($this->classFile())
            ->implode('/');
    }

    public function relativeClassPath()
    {
        return Str::replaceFirst(base_path() . DIRECTORY_SEPARATOR, '', $this->classPath());
    }

    public function classFile()
    {
        return $this->componentClass . '.php';
    }

    public function classNamespace()
    {
        return empty($this->directories)
            ? $this->baseClassNamespace
            : $this->baseClassNamespace . '\\' . collect()
                ->concat($this->directories)
                ->map([Str::class, 'studly'])
                ->implode('\\');
    }

    public function className()
    {
        return $this->componentClass;
    }

    public function classContents()
    {
        if ($this->model) {
            $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'datatable-model.stub');

            return preg_replace_array(
                ['/\[namespace\]/', '/\[use\]/', '/\[class\]/', '/\[model\]/'],
                [
                    $this->classNamespace(),
                    config('livewire-datatables.model_namespace', 'App') . '\\' . Str::studly($this->model),
                    $this->className(),
                    Str::studly($this->model),
                ],
                $template
            );
        } else {
            $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'datatable.stub');

            return preg_replace_array(
                ['/\[namespace\]/', '/\[class\]/'],
                [$this->classNamespace(), $this->className()],
                $template
            );
        }
    }

    public static function generatePathFromNamespace($namespace)
    {
        $name = Str::replaceFirst(app()->getNamespace(), '', $namespace);

        return app('path') . '/' . str_replace('\\', '/', $name);
    }
}
