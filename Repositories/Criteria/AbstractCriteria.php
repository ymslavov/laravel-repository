<?php
namespace YasenSlavov\LaravelRepository\Repositories\Criteria;

use YasenSlavov\LaravelRepository\Repositories\Interfaces\RepositoryInterface;

abstract class AbstractCriteria
{
    /**
     * Apply the criteria to the query builder
     *
     * @param $builder
     * @param RepositoryInterface $repository
     * @return mixed
     */
    abstract public function apply($builder, RepositoryInterface $repository);
}