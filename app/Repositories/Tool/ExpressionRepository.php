<?php

namespace App\Repositories\Tool;

use App\Models\Tool\Expression;
use App\Repositories\EloquentRepository;

class ExpressionRepository extends EloquentRepository
{

    public function __construct(Expression $expression)
    {
        $this->model = $expression;
    }

    /**
     * 获取表指定列的全部有效数据
     *
     * @param array $columns
     * @return mixed
     */
    public function getAll(array $columns = ['*'])
    {
        return $this->model->where('is_delete', '=', 0)->get($columns);
    }
}