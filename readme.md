# Stateful Repository Implementation

## Based on the work featured in [bosnadev.com]( https://bosnadev.com/2015/03/07/using-repository-pattern-in-laravel-5/), but with a few additional helpful methods for multi-record operations.

### Laravel 5.2+ required.

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