# Stateful Repository Implementation

Based on the work featured in [bosnadev.com]( https://bosnadev.com/2015/03/07/using-repository-pattern-in-laravel-5/), but with a few additional helpful methods for multi-record operations.

Laravel 5.3+ required.

## Installation via Composer
```
composer require ymslavov/laravel-repository
```

## Using Repository Generators
The package provides an easy way to generate repository classes for all models in the system.

All you need to do is register the service provider in your config/app.php in the 'providers' array:
```
'providers' => [
    ...other providers here...,
    YasenSlavov\LaravelRepository\Providers\LaravelRepositoryServiceProvider::class
]
```

And then use the following command in your command line tool:
```
php artisan repositories:generate
```

The script will scan the app/ directory for any classes that extend the Eloquent Model class and create repository classes for them.

## Extending the AbstractRepository directly
If you prefer not to use the auto-generated classes, you can simply extend the AbstractRepository class directly from the package.

Example:
```
class UsersRepository extends AbstractRepository {
  /**
     * Specify the fully-qualified model name. Best use Classname::class
     *
     * @return string
     */
    function model()
    {
        return User::class;
    }
}


$usersRepo = \App::make(UsersRepository::class);

$usersWithTitleManager = $usersRepo
                            ->clearScope() //clear any state already established in the repo object
                            ->pushCriteria(new ByRoleTitle('Manager'))
                            ->all();

```