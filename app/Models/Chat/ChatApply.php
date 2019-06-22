<?php

namespace App\Models\Chat;

use App\Libs\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Model;

class ChatApply extends Model
{
    use BaseModelTrait;

    protected $table = 'chat_apply';

    protected $primaryKey = 'id';

    protected $fillable = [
        'apply_user_id', 'friend_id', 'group_id', 'remarks', 'apply_status', 'apply_time', 'audit_time'
    ];

    public $timestamps = false;

    /**
     * 申请用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applyUser()
    {
        return $this->belongsTo('App\Models\Chat\User', 'apply_user_id', 'id');
    }

    /**
     * 目标用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function friendUser()
    {
        return $this->belongsTo('App\Models\Chat\User', 'friend_id', 'id');
    }

    /**
     * 目标群
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Chat\ChatGroup', 'group_id', 'group_id');
    }
}
