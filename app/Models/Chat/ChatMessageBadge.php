<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatMessageBadge extends Model
{
    protected $table = 'chat_message_badge';

    protected $primaryKey = 'id';

    protected $fillable = [
        'chat_id', 'user_id', 'count'
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
     * 好友
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chatUser()
    {
        return $this->belongsTo('App\Models\Chat\ChatUsers', 'chat_id', 'id');
    }
}
