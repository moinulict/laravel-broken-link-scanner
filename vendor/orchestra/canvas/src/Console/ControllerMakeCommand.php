<?php

namespace Orchestra\Canvas\Console;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Canvas\Core\Concerns\CodeGenerator;
use Orchestra\Canvas\Core\Concerns\TestGenerator;
use Orchestra\Canvas\Core\Concerns\UsesGeneratorOverrides;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Routing/Console/ControllerMakeCommand.php
 */
#[AsCommand(name: 'make:controller', description: 'Create a new controller class')]
class ControllerMakeCommand extends \Illuminate\Routing\Console\ControllerMakeCommand
{
    use CodeGenerator;
    use TestGenerator;
    use UsesGeneratorOverrides;

    /**
     * Create a new creator command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->addGeneratorPresetOptions();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    #[\Override]
    public function handle()
    {
        return $this->generateCode() ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     */
    #[\Override]
    protected function qualifyModel(string $model)
    {
        return $this->qualifyModelUsingCanvas($model);
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    #[\Override]
    protected function buildParentReplacements()
    {
        $parentModelClass = $this->parseModel($this->option('parent'));

        if (! class_exists($parentModelClass) &&
            $this->components->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
            $this->call('make:model', ['name' => $parentModelClass, '--preset' => $this->option('preset')]);
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    #[\Override]
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        if (! class_exists($modelClass) && $this->components->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
            $this->call('make:model', ['name' => $modelClass, '--preset' => $this->option('preset')]);
        }

        $replace = $this->buildFormRequestReplacements($replace, $modelClass);

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    #[\Override]
    protected function getPath($name)
    {
        return $this->getPathUsingCanvas($name);
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    #[\Override]
    protected function rootNamespace()
    {
        return $this->rootNamespaceUsingCanvas();
    }
}
