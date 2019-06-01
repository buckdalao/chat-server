<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatGroupMessageBadge extends Model
{
    protected $table = 'chat_group_message_badge';

    protected $primaryKey = 'id';

    protected $fillable = [
        'group_id', 'user_id', 'count'
    ];

    /**
     * 用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Chat\User', 'user_id', 'id');
    }

    /**
     * 群
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Chat\ChatGroup', 'group_id', 'id');
    }
}
