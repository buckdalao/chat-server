<?php

namespace App\Models\Chat;

use App\Libs\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Model;

class ChatUsersMessage extends Model
{
    use BaseModelTrait;

    protected $primaryKey = 'user_mes_id';

    protected $fillable = [
        'chat_id', 'user_id', 'to_user_id', 'content', 'send_time', 'mes_type', 'status'
    ];

    protected $table = 'chat_users_message';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chatUser()
    {
        return $this->belongsTo('App\Models\Chat\ChatUsers', 'chat_id', 'id');
    }
}
