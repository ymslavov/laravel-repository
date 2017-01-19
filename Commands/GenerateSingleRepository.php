<?php

namespace YasenSlavov\LaravelRepository\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateSingleRepository extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'repositories:generate-single';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a single repository for a model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = null;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->type = $this->argument('name');

        $name = $this->parseName($this->getNameInput());

        $path = $this->getPath($name);

        if ($this->alreadyExists($this->getNameInput())) {
            $this->line($this->type.' already exists! Skipping.');
            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace('DummyModelClass', $this->option('model'), $stub);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the command.'],
        ];
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/Repository.stub';
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'model', 'm', InputOption::VALUE_REQUIRED, 'Specify the model for which a new repository will be created.'
            ],
        ];
    }
}
