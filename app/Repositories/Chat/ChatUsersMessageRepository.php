<?php

namespace App\Repositories\Chat;

use App\Libs\ArrayPaginator;
use App\Libs\Traits\WsMessageTrait;
use App\Libs\Upload\UploadFactory;
use App\Models\Chat\ChatUsers;
use App\Models\Chat\ChatUsersMessage;
use App\Models\Chat\User;
use App\Repositories\EloquentRepository;

class ChatUsersMessageRepository extends EloquentRepository
{
    use WsMessageTrait;

    protected $chatUserModel;

    protected $user;

    public function __construct(ChatUsersMessage $model, ChatUsers $chatUsers, User $user)
    {
        $this->model = $model;
        $this->chatUserModel = $chatUsers;
        $this->user = $user;
    }

    /**
     * @param $json
     * @return bool
     */
    public function saveExpire($json)
    {
        $data = json_decode($json, true);
        if ($data['uid'] && $data['chat_id']) {
            $chat = $this->chatUserModel->newQuery()->find($data['chat_id']);
            $toUid = $chat->user_id_1 == $data['uid'] ? $chat->user_id_2 : $chat->user_id_1;
            if ($chat) {
                $this->model->newQuery()->insert([
                    'chat_id'    => $chat->id,
                    'to_user_id' => $toUid,
                    'user_id'    => $data['uid'],
                    'content'    => $data['data'],
                    'send_time'  => $data['time'],
                    'mes_type'   => $data['type'] ?: 0
                ]);
            }
        }
        return true;
    }

    /**
     * 一对一聊天消息
     *
     * @param     $chatId
     * @param int $limit
     * @return array
     */
    public function chatMessage($chatId, $limit = 0)
    {
        $mes = [];
        if ($chatId) {
            $mes = $this->setChatId($chatId)->getMessage();
            if (sizeof($mes) == 0) {
                $mes = $this->model->newQuery()->from($this->model->alias('cum'))->
                leftJoin($this->user->alias('u'), 'u.id', '=', 'cum.user_id')
                    ->where('cum.chat_id', '=', $chatId)->orderBy('cum.' . $this->model->getKeyName(), 'asc')
                    ->select(['cum.user_mes_id', 'cum.chat_id', 'cum.content as data', 'cum.mes_type as type', 'cum.send_time as time',
                        'cum.status', 'cum.user_id as uid', 'cum.to_user_id', 'u.name as user_name', 'u.photo'])->paginate($limit);
                if ($mes) {
                    collect($mes->items())->map(function ($item) {
                        if ($item->photo) {
                            $item->photo = asset($item->photo);
                        }
                        if ($item->type == 7 && $item->data) {
                            $item->data = UploadFactory::mediaUrl($item->data, 'audio');
                        }
                        if ($item->type == 4 && $item->data) {
                            $item->data = asset($item->data);
                        }
                    });
                }
            } else {
                $class = new ArrayPaginator();
                $mes = $class->setArray($mes)->paginate($limit);
            }
        }
        return $mes;
    }
}