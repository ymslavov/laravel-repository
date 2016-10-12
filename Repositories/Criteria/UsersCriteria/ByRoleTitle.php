<?php
namespace App\Core\Repositories\Eloquent\Criteria\UsersCriteria;

use App\Core\Repositories\Interfaces\RepositoryInterface;
use App\Core\Repositories\Eloquent\Criteria\AbstractCriteria;

class ByRoleTitle extends AbstractCriteria
{
    /**
     * @var
     */
    protected $title;

    /**
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * @param $builder
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($builder, RepositoryInterface $repository)
    {
        return $builder->where('title', $this->title);
    }


}