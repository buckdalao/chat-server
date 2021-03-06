<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $primaryKey = 'group_id';

    protected $table = 'chat_group';

    protected $fillable = [
        'group_name', 'group_status', 'user_id'
    ];

    /**
     * 群主信息  关联user模型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groupOwner()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    /**
     * 群消息 关联 chat group message model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function message()
    {
        return $this->hasMany('App\Models\Chat\ChatGroupMessage', 'group_id', 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMembers()
    {
        return $this->hasMany('App\Models\Chat\ChatGroupUser', 'group_id','group_id');
    }
}
