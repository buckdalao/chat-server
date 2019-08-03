<?php

namespace App\Libs;


use Illuminate\Pagination\LengthAwarePaginator;

class ArrayPaginator
{
    protected $data;
    protected $currentPage;

    public function setArray(array $data)
    {
        $this->data = $data;
        return $this;
    }

    protected function splitArray($limit)
    {
        $this->currentPage = request()->get('page') ?: null;
        return collect($this->data)->slice(($this->currentPage - 1) * $limit, $limit)->all();
    }

    public function paginate($limit)
    {
        $limit = $limit ?: 15;
        $item = $this->splitArray($limit);
        $paginate = new LengthAwarePaginator(array_values($item), sizeof($this->data), $limit, $this->currentPage);
        return $paginate->withPath(\request()->path());
    }
}