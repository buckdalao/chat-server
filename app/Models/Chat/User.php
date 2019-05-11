<?php

namespace App\Models\Chat;

use App\User as BaseUser;

class User extends BaseUser
{
    /**
     * 用户所属的群
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userGroup()
    {
        return $this->hasMany('App\Models\Chat\ChatGroup', 'id','user_id');
    }
}
