<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatMessageBadge;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class ChatMessageBadgeRepository  extends EloquentRepository
{

    public function __construct(ChatMessageBadge $model)
    {
        $this->model = $model;
    }

    public function setBadge($uid, $chatId)
    {
        if (empty($uid) || empty($chatId)) {
            return ;
        }
        $badge = $this->model->newQuery()->where('user_id', '=', $uid)->where('chat_id', '=', $chatId)->first(['id', 'count']);
        if ($badge && $badge->id) {
            $this->model->newQuery()->whereKey($badge->id)->update([
                'count' => DB::raw('count + 1')
            ]);
        } else {
            $this->model->newQuery()->insert([
                'user_id' => $uid,
                'chat_id' => $chatId,
                'count' => 1
            ]);
        }
    }

    public function resetBadge($uid, $chatId)
    {
        $this->model->newQuery()->where('user_id', '=', $uid)->where('chat_id', '=', $chatId)->update([
            'count' => 0
        ]);
    }

    /**
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getBadgeByUid($uid)
    {
        if ($uid) {
            return $badge = $this->model->newQuery()->where('user_id', '=', $uid)->get(['chat_id', 'count']);
        }
    }
}