<?php
namespace App\Core\Repositories\Eloquent;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use App\Core\Repositories\Interfaces\CriteriaInterface;
use App\Core\Repositories\Interfaces\RepositoryInterface;
use App\Core\Repositories\Eloquent\Criteria\AbstractCriteria;
use App\Core\Repositories\Exceptions\ModelNotFoundRepositoryException;

abstract class AbstractRepository implements RepositoryInterface, CriteriaInterface
{
    /**
     * An instance of the IC container
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * An instance of the model query builder, to which the repository belongs/is responsible for
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * The model for the repo
     *
     * @var Model
     */
    protected $model;

    /**
     * An array holding all pushed criteria
     *
     * @var array
     */
    protected $criteria = [];

    /**
     * Constructor
     *
     * @param \Illuminate\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->builder = $this->makeBuilder();
    }

    /**
     * Specify the fully-qualified model name. Best use ModelClassName::class
     *
     * @return string
     */
    abstract function model();

    /**
     * Create an instance of the model and return it
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws ModelNotFoundRepositoryException
     */
    protected function makeBuilder()
    {
        $this->model = $this->app->make($this->model());

        if (!$this->model instanceof Model) {
            throw new ModelNotFoundRepositoryException(
                "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }

        return $this->model->newQuery();
    }

    /**
     * A getter for the Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Fetch all model records and return the specified columns only
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();

        return $this->builder->get($columns);
    }

    /**
     * Fetch all model records and paginate them by X records per page, whilst returning the specified columns only.
     * The returned object is iterable and each iteration contains X model records.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        $this->applyCriteria();

        return $this->builder->paginate($perPage, $columns);
    }

    /**
     * Create a new record in the database
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes = [])
    {
        if (isset($attributes[0]) && is_array($attributes[0])) { //check if there are nested array, if yes, treat them as an insert of multiple records
            return $this->builder->getQuery()->insert($attributes);
        }

        return $this->builder->getModel()->create($attributes);
    }

    /**
     * Update a record with an id (or a set of ids, listed in an array) in the database
     *
     * @param int|array $id
     * @param array $attributes
     * @param string $byAttribute
     * @return bool|int
     */
    public function update($id, array $attributes, $byAttribute = 'id')
    {
        return is_array($id) ?
            $this->builder->whereIn($byAttribute, $id)->update($attributes)
            :
            $this->builder->where($byAttribute, $id)->update($attributes);
    }

    /**
     * Delete a record by an id (or a set of ids, listed in an array)
     *
     * @param int|array $id
     * @param string $byAttribute
     * @return bool|null
     */
    public function delete($id, $byAttribute = 'id')
    {
        return is_array($id) ?
            $this->builder->whereIn($byAttribute, $id)->delete()
            :
            $this->builder->where($byAttribute, $id)->delete();
    }

    /**
     * Find a record (or records if $id is an array) in the database by id and return the specified columns only
     *
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();

        return $this->builder->findOrFail($id);
    }

    /**
     * Find a record in the database by a specified field and return the specified columns only
     *
     * @param $field
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findBy($field, $value, $columns = ['*'])
    {
        $this->applyCriteria();

        return is_array($value) ? $this->builder->whereIn($field, $value)->get($columns) : $this->builder->where($field,
            $value)->first($columns);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param $column
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function sum($column)
    {
        $this->applyCriteria();

        return $this->builder->sum($column);
    }

    /**
     * Get last X records from the database, grouped by a column
     *
     * @param $count
     * @param $groupByColumn
     * @param null $whereColumn
     * @param string $operator
     * @param null $value
     * @return mixed
     */
    public function lastGrouped($count, $groupByColumn, $whereColumn = null, $operator = '=', $value = null)
    {
        $modelTable = $this->model->getTable();

        \DB::statement(\DB::raw("set @num := 0, @$groupByColumn:= ''"));

        $q = \DB::select(\DB::raw(
            "select *,
              @num := if(@$groupByColumn = $groupByColumn, @num + 1, 1) as row_number,
              @opta_person_id := $groupByColumn as dummy
            from
            (select *
            from $modelTable
            order by $groupByColumn, created_at desc) T
            group by id, $groupByColumn, created_at
           
            having row_number <= $count " . (!is_null($whereColumn) ? "AND $whereColumn $operator $value" : "") . ";
            "));

        return (new $this->model())->hydrate($q);
    }

    /**
     * Return an (associative, if $key is specified) array of the $column values in the db
     *
     * @param $column
     * @param null $key
     * @return array
     */
    public function pluck($column, $key = null)
    {
        $this->applyCriteria();

        return $this->builder->pluck($column, $key)->toArray();
    }

    /**
     * Get an array of one, or many, attribute sets to be inserted into the database, only if they don't already exist there
     *
     * WARNING: The INSERT query for a record must throw DUPLICATE KEY EXCEPTION if you want it to be ignored if it exists.
     * I.e. at least one of the inserted values must be of a primary or unique column
     *
     * @param array $attributes
     */
    public function insertWhereNotExist(array $attributes)
    {
        if (isset($attributes[0]) && !is_array($attributes[0])) {
            $attributes = [$attributes];
        }

        //handle as an array of attribute sets
        $valuesString = '';

        $valuesString .= implode(',', array_map(function ($set) {
            return "(" . implode(',', array_map(function ($value) {
                return "'$value'";
            }, $set)) . ")";
        }, $attributes));

        $columnsString = implode(',', array_keys($attributes[0]));

        \DB::select(\DB::raw("
            INSERT IGNORE INTO {$this->model->getTable()} ($columnsString) VALUES 
            $valuesString
        "));
    }

    /**
     * Update many records at once with different values
     *
     * @param int $id_column A unique or primary column in the table
     * @param array $columns A list of all other column names (apart from the id column) to be updated
     * @param array $values A list of arrays in the form [[{primaryId},{value1},{value2},{value3}], [{primaryId},{value1},{value2},{value3}],...]
     */
    public function updateMany($id_column, array $columns, array $values)
    {
        $endValuesString = '';

        foreach ($columns as $column) {
            $endValuesString .= "$column=VALUES($column),";
        }
        $endValuesString = substr($endValuesString, 0, strlen($endValuesString) - 1);

        \DB::select(\DB::raw("
            INSERT INTO {$this->model->getTable()}
            ($id_column, " . implode(',', $columns) . ")
            VALUES
            " . multi_implode($values, ',') . "
            ON DUPLICATE KEY UPDATE
            $endValuesString
        "));
    }

    /**
     * Determine if a record with that id (or alternative column) exists
     *
     * @param $value
     * @param string $column
     * @return bool
     */
    public function exists($value, $column = 'id')
    {
        $this->applyCriteria();

        return ($this->builder->where($column, $value)->count() > 0) ? true : false;
    }

    /**
     * Removes all criteria from the repository
     *
     * @return $this
     */
    public function clearScope()
    {
        $this->criteria = [];
        $this->builder = $this->model->newQuery();

        return $this;
    }

    /**
     * A getter for all pushed criteria
     *
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function pushCriteria(AbstractCriteria $criteria)
    {
        $this->criteria[] = $criteria;

        return $this;
    }

    /**
     * @return $this
     */
    public function applyCriteria()
    {
        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof AbstractCriteria) {
                $this->builder = $criteria->apply($this->builder, $this);
            }
        }

        return $this;
    }
}
