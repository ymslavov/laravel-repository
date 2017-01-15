<?php

namespace YasenSlavov\LaravelRepository\Providers;

use Illuminate\Support\ServiceProvider;
use YasenSlavov\LaravelRepository\Commands\GenerateRepositories;
use YasenSlavov\LaravelRepository\Commands\GenerateSingleRepository;

class LaravelRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register commands to generate repository class files
     */
    public function register()
    {
        $this->commands([GenerateRepositories::class, GenerateSingleRepository::class]);
    }
}