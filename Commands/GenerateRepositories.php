<?php

namespace YasenSlavov\LaravelRepository\Commands;

use hanneskod\classtools\Iterator\ClassIterator;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class GenerateRepositories extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'repositories:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate repository classes from the models in your app root namespace.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'BaseRepository';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $name = $this->parseName($this->getNameInput());

        $path = $this->getPath($name);

        if ($this->alreadyExists($this->getNameInput())) {
            $this->line($this->type.' already exists! Skipping.');
        }else{
            $this->makeDirectory($path);

            $this->files->put($path, $this->buildClass($name));

            $this->info($this->type.' created successfully.');
        }


        $finder = new Finder();
        $iter = new ClassIterator($finder->files()->name('*.php')->in('app'));
        $iter->enableAutoloading();

        // Print the file names of classes, interfaces and traits in 'src'
        foreach ($iter as $classname) {
            if($classname->isSubclassOf(Model::class) && $classname->isInstantiable())
                $this->call('repositories:generate-single', [
                    'name' => Str::plural(class_basename($classname->getName())).'Repository',
                    '--model' => class_basename($classname->getName()),
                ]);

        }

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/BaseRepository.stub';
    }


    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function getNameInput()
    {
        return $this->type;
    }
}
