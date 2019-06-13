<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2019/5/17
 * Time: 17:04
 */

namespace App\Libs\Traits;


trait BaseModelTrait
{
    public function alias($as)
    {
        return $this->getTable() . ' as ' . $as;
    }
}