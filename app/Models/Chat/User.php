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
        return $this->hasMany('App\Models\Chat\ChatGroup', 'user_id', 'id');
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

    /**
     * 加群或加好友的申请人
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applyUser()
    {
        return $this->hasMany('App\Models\Chat\ChatApply', 'apply_user_id', 'id');
    }

    /**
     * 好友验证
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditUser()
    {
        return $this->hasMany('App\Models\Chat\ChatApply', 'friend_id', 'id');
    }

    /**
     * 对话消息提醒个数
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chatMessBadge()
    {
        return $this->hasMany('App\Models\Chat\ChatMessageBadge', 'user_id', 'id');
    }

    /**
     * 群信息提醒个数
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMessBadge()
    {
        return $this->hasMany('App\Models\Chat\ChatGroupMessageBadge', 'user_id', 'id');
    }

    /**
     * 用户通知提醒个数
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userNotifyBadge()
    {
        return $this->hasMany('App\Models\Chat\UserNotifyBadge', 'user_id', 'id');
    }
}
