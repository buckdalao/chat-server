<?php

namespace App\Repositories\Chat;

use App\Libs\Traits\WsMessageTrait;
use App\Models\Chat\ChatGroupMessage;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\Redis;

class ChatGroupMessageRepository  extends EloquentRepository
{
    use WsMessageTrait;

    public function __construct(ChatGroupMessage $model)
    {
        $this->model = $model;
    }

    /**
     * 释放redis中缓存的消息，保存到数据库
     * 注意区别于溢出的消息，保存不同的key.溢出的消息是自动保存
     *
     * @param null $key
     */
    public function resetTemporary($key = null)
    {
        $key = $key ?: $this->getKey();
        if ($key) {
            $len = Redis::llen($key);
            for ($i=0; $i < $len; $i++){
                $str = Redis::lpop($key);
                $data = json_encode($str, true);
                $this->model->create([
                    'group_id'  => $data['groupId'],
                    'user_id'   => $data['uid'],
                    'content'   => $data['data'],
                    'send_time' => $data['time'],
                    'mes_type'  => $data['type'] ?: 0
                ]);
            }
        }
    }

    /**
     * 获取群消息 redis或者db中
     *
     * @param     $groupID
     * @param int $limit
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getCurrentMessage($groupID, $limit = 0)
    {
        $mes = $this->setGroupId($groupID)->getMessage();
        if (sizeof($mes)) {
            return $mes;
        }else{
            if ($limit){
                $mes = $this->model->newQuery()->where('group_id', $groupID)->orderBy($this->model->getKeyName(), 'desc')->limit($limit)->get();
            }else {
                $mes = $this->model->newQuery()->where('group_id', $groupID)->orderBy($this->model->getKeyName(), 'desc')->get();
            }
            return $mes;
        }
    }
}