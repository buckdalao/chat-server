<?php

namespace App\Models\Chat;

use App\Libs\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Model;

class ChatUsers extends Model
{
    use BaseModelTrait;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id_1', 'user_id_2', 'status'
    ];

    protected $table = 'chat_users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function message()
    {
        return $this->hasMany('App\Models\Chat\ChatUsersMessage', 'chat_id', 'id');
    }
}
