<?php

namespace App\Core\Repositories\Interfaces;


interface RepositoryInterface
{
    public function all($columns = ['*']);

    public function paginate($perPage = 15, $columns = ['*']);

    public function create(array $data);

    public function update($id, array $data, $byAttribute = 'id');

    public function delete($id, $byAttribute = 'id');

    public function find($id, $columns = ['*']);

    public function findBy($field, $value, $columns = ['*']);
}