<?php
namespace YasenSlavov\Interfaces;

use YasenSlavov\Criteria\AbstractCriteria;

/**
 * To be implemented by various AbstractRepositories. Used for work with search criteria
 *
 * Interface CriteriaInterface
 * @package App\Core\Repositories\Interfaces
 */
interface CriteriaInterface
{

    /**
     * @return array
     */
    public function getCriteria();

    /**
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function pushCriteria(AbstractCriteria $criteria);

    /**
     * @return $this
     */
    public function applyCriteria();

    /**
     * @return $this
     */
    public function clearScope();
}