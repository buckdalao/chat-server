<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatGroupUser extends Model
{
    protected $primaryKey = 'group_user_id';

    protected $table = 'chat_group_users';

    protected $fillable = [
        'group_id', 'user_id', 'status', 'group_user_name'
    ];

    /**
     * 关联群信息表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Chat\ChatGroup', 'group_id', 'group_id');
    }

    /**
     * 关联用户表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
