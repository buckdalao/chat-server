<?php

namespace App\Models\Chat;

use App\Libs\Traits\BaseModelTrait;
use App\User as BaseUser;

class User extends BaseUser
{
    use BaseModelTrait;

    /**
     * 用户所拥有的群
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupOwner()
    {
        return $this->hasMany('App\Models\Chat\ChatGroup', 'user_id','id');
    }

    /**
     * 用户所属群
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupUser()
    {
        return $this->hasMany('App\Models\Chat\ChatGroupUser', 'user_id', 'id');
    }
}
