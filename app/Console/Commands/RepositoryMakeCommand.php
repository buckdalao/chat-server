<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';


    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return base_path('resources/stubs/repository.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $modelName = explode('/', $this->setModel());
        $stub = str_replace(
            ['RepositoryNamespace', 'DummyNamespace', 'ModelName'],
            [$this->getNamespace($name), str_replace('/', '\\', $this->setModel()), $modelName[count($modelName) - 1]],
            $stub
        );

        return $this;
    }


    /**
     * set Model
     *
     */
    private function setModel()
    {
        if (!empty($this->option('model'))) {
            return $this->option('model');
        } else {
            $name = explode('/', $this->getNameInput('name'));
            return str_replace('Repository', '', $name[count($name) - 1]);
        }
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Injection  model.']
        ];
    }
}
