<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository
{
    /**
     * The repository model class.
     *
     * @var object
     */
    protected $model;

    /**
     * Magic callStatic method to forward static methods to the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([new static(), $method], $parameters);
    }

    /**
     * Magic call method to forward methods to the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $model = $this->model;

        return call_user_func_array([$model, $method], $parameters);
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param mixed $relationships
     *
     * @return $this
     */
    public function with($relationships)
    {
        $this->model = $this->model->with($relationships);
        return $this;
    }

    /**
     * Add an "order by" clause to the repository instance.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }

    /**
     * Add an "data permission scope" clause to the repository instance.
     *
     * @param $field
     *
     * @return $this
     */
    public function dataPermission($field)
    {
        $this->model = $this->model->dataPermission($field);
        return $this;
    }

    /**
     * Find an entity by its primary key.
     *
     * @param int   $id
     * @param array $columns
     * @param array $with
     */
    public function find($id, $columns = ['*'], $with = [])
    {
        return $this->model->with($with)->find($id, $columns);
    }

    /**
     * Find the entity by the given attribute.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $columns
     * @param array  $with
     */
    public function findBy($attribute, $value, $columns = ['*'], $with = [])
    {
        return $this->model->with($with)->where($attribute, '=', $value)->first($columns);
    }

    /**
     * Find all entities.
     *
     * @param array $columns
     * @param array $with
     */
    public function findAll($columns = ['*'], $with = [])
    {
        return $this->model->with($with)->get($columns);
    }

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     * @param array $columns
     * @param array $with
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhere($where, $columns = ['*'], $with = [])
    {
        $where = $this->castRequest($where);
        $model = $this->model instanceof Model ? $this->model->query() : $this->model;
        foreach ($where as $attribute => $value) {
            if (is_array($value)) {
                list($attribute, $condition, $value) = $value;
                $model->where($attribute, $condition, $value);
            } else {
                $model->where($attribute, '=', $value);
            }
        }
        return $model->with($with)->get($columns);
    }

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhereTo($where)
    {
        $where = $this->castRequest($where);
        $this->model = $this->model instanceof Model ? $this->model->query() : $this->model;
        foreach ($where as $attribute => $value) {
            if (is_array($value)) {
                list($attribute, $condition, $value) = $value;
                $this->model->where($attribute, $condition, $value);
            } else {
                $this->model->where($attribute, '=', $value);
            }
        }
        return $this->model;
    }

    /**
     * Find all entities matching whereBetween conditions.
     *
     * @param  string $attribute
     * @param  array  $values
     * @param  array  $columns
     * @param  array  $with
     */
    public function findWhereBetween($attribute, $values, $columns = ['*'], $with = [])
    {
        $values = $this->castRequest($values);
        return $this->model->with($with)->whereBetween($attribute, $values)->get($columns);
    }

    /**
     * Find all entities matching whereIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     * @param array  $with
     */
    public function findWhereIn($attribute, $values, $columns = ['*'], $with = [])
    {
        $values = $this->castRequest($values);
        return $this->model->with($with)->whereIn($attribute, $values)->get($columns);
    }

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     * @param array  $with
     */
    public function findWhereNotIn($attribute, $values, $columns = ['*'], $with = [])
    {
        $values = $this->castRequest($values);
        return $this->model->with($with)->whereNotIn($attribute, $values)->get($columns);
    }

    /**
     * Find an entity matching the given attributes or create it.
     *
     * @param array $attributes
     *
     * @return array|mixed
     */
    public function findOrCreate($attributes)
    {
        $attributes = $this->castRequest($attributes);

        if (!is_null($instance = $this->findWhere($attributes)->first())) {
            return $instance;
        }

        return $this->create($attributes);
    }

    /**
     * Get an array with the values of the given column from entities.
     *
     * @param string      $column
     * @param string|null $key
     */
    public function pluck($column, $key = null)
    {
        return $this->model->pluck($column, $key);
    }

    /**
     * Paginate the given query for retrieving entities.
     *
     * @param null   $perPage
     * @param array  $columns
     * @param string $pageName
     * @param null   $page
     * @param array  $with
     *
     * @return mixed
     */
    public function paginate($perPage = null, $columns = ['*'], $with = [], $pageName = 'page', $page = null)
    {
        return $this->model->with($with)->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Create a new entity with the given attributes.
     *
     * @param mixed $attributes
     *
     * @return array
     */
    public function create($attributes)
    {
        $attributes = $this->castRequest($attributes);
        $instance = $this->model->newInstance($attributes);
        $created = $instance->save();
        return [
            $created,
            $instance,
        ];
    }

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     */
    public function update($id, $attributes)
    {
        $attributes = $this->castRequest($attributes);
        $updated = false;
        $instance = $id instanceof Model ? $id : $this->find($id);
        if ($instance) {
            $updated = $instance->update($attributes);
        }
        return [
            $updated,
            $instance,
        ];
    }

    /**
     * Delete an entity with the given ID.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function delete($id)
    {
        $deleted = false;
        $instance = $id instanceof Model ? $id : $this->find($id);
        if ($instance) {
            $deleted = $instance->delete();
        }
        return [
            $deleted,
            $instance,
        ];
    }

    /**
     * @param Model $parent
     *
     * @return string
     */
    public function getHighIdsFromParentID(Model $parent): string
    {
        $parent_high_ids = $parent->high_ids;
        if ((int)$parent_high_ids) {
            $high_ids = $parent_high_ids . ',' . "$parent->id";
        } else {
            $high_ids = "$parent->id";
        }
        return $high_ids;
    }

    /**
     * Cast HTTP request object to an array if need be.
     *
     * @param array|Request $request
     *
     * @return array
     */
    protected function castRequest($request)
    {
        return $request instanceof Request ? $request->all() : $request;
    }
}
