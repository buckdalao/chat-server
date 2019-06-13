<?php

namespace App\Repositories\Chat;

use App\Libs\Traits\WsMessageTrait;
use App\Libs\Upload\UploadFactory;
use App\Models\Chat\ChatGroupMessage;
use App\Models\Chat\ChatGroupUser;
use App\Models\Chat\User;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\Redis;

class ChatGroupMessageRepository extends EloquentRepository
{
    use WsMessageTrait;

    protected $chatGroupUser;

    protected $user;

    public function __construct(ChatGroupMessage $model, ChatGroupUser $chatGroupUser, User $user)
    {
        $this->model = $model;
        $this->chatGroupUser = $chatGroupUser;
        $this->user = $user;
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
            for ($i = 0; $i < $len; $i++) {
                $str = Redis::lpop($key);
                $data = json_encode($str, true);
                $this->model->create([
                    'group_id'  => $data['group_id'],
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
    public function getGroupMessage($groupID, $limit = 0)
    {
        $mes = $this->setGroupId($groupID)->getMessage();
        if (sizeof($mes)) {
            return $mes;
        } else {
            $mes = $this->model->newQuery()->from($this->model->alias('cgm'))
                ->leftJoin($this->chatGroupUser->alias('cgu'), function ($join) {
                    $join->on('cgm.group_id', '=', 'cgu.group_id')->on('cgm.user_id', '=', 'cgu.user_id');
                })
                ->leftJoin($this->user->alias('u'), function ($join) {
                    $join->on('cgm.user_id', '=', 'u.id');
                })
                ->where('cgm.group_id', $groupID)->orderBy($this->model->getKeyName(), 'asc')->limit($limit ?: 50)->get([
                    'cgm.group_mes_id', 'cgm.group_id', 'cgm.content as data', 'cgm.send_time as time', 'cgm.mes_type as type', 'cgm.user_id as uid', 'cgm.status',
                    'cgu.group_user_name as user_name', 'u.photo'
                ]);
            if ($mes) {
                collect($mes)->map(function ($item) {
                    if ($item->photo) {
                        $item->photo = asset($item->photo);
                        if ($item->type == 7 && $item->data) {
                            $item->data = UploadFactory::mediaUrl($item->data, 'audio');
                        }
                    }
                });
            }
            return $mes;
        }
    }
}