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

    public function upBadge($uid, $chatId, $qty = 1)
    {
        if (empty($uid) || empty($chatId)) {
            return ;
        }
        $badge = $this->model->newQuery()->where('user_id', '=', $uid)->where('chat_id', '=', $chatId)->first(['id', 'count']);
        if ($badge && $badge->id) {
            $this->model->newQuery()->whereKey($badge->id)->update([
                'count' => DB::raw('count + ' . $qty)
            ]);
        } else {
            $this->model->newQuery()->insert([
                'user_id' => $uid,
                'chat_id' => $chatId,
                'count' => $qty
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

    public function setBadgeCount($uid, $chatId, $count)
    {
        if ($this->issetBadge($uid, $chatId)) {
            $this->model->newQuery()->where('user_id', '=', $uid)->where('chat_id', '=', $chatId)->update([
                'count' => (int)$count
            ]);
        } else {
            $this->upBadge($uid, $chatId, $count);
        }
    }

    public function issetBadge($uid, $chatId)
    {
        return $this->model->newQuery()->where('user_id', '=', $uid)->where('chat_id', '=', $chatId)->exists();
    }
}