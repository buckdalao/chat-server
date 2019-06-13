<?php

namespace App\Models\Chat;

use App\Libs\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Model;

class ChatGroupMessage extends Model
{
    use BaseModelTrait;

    protected $primaryKey = 'group_mes_id';

    protected $table = 'chat_group_message';

    protected $fillable = [
        'group_id', 'user_id', 'content', 'send_time', 'mes_type', 'status'
    ];

    /**
     * 群信息  关联chat group模型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Chat\ChatGroup', 'group_id', 'group_id');
    }

    /**
     * 保存redis中的溢出消息到数据库
     *
     * @param $json
     * @return bool
     */
    public function saveExpire($json)
    {
        $data = json_decode($json, true);
        if ($data['uid'] && $data['group_id']) {
            $this->create([
                'group_id'  => $data['group_id'],
                'user_id'   => $data['uid'],
                'content'   => $data['data'],
                'send_time' => $data['time'],
                'mes_type'  => $data['type'] ?: 0
            ]);
        }
        return true;
    }
}
