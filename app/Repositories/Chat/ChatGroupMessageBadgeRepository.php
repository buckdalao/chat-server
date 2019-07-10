<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatGroupMessageBadge;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class ChatGroupMessageBadgeRepository extends EloquentRepository
{

    public function __construct(ChatGroupMessageBadge $model)
    {
        $this->model = $model;
    }

    /**
     * @param $uid
     * @param $groupId
     */
    public function upBadge($uid, $groupId, $qty = 1)
    {
        if (empty($uid) || empty($groupId)) {
            return;
        }
        $badge = $this->model->newQuery()->where('user_id', '=', $uid)->where('group_id', '=', $groupId)->first(['id', 'count']);
        if ($badge && $badge->id) {
            $this->model->newQuery()->whereKey($badge->id)->update([
                'count' => DB::raw('count + ' . $qty)
            ]);
        } else {
            $this->model->newQuery()->insert([
                'user_id'  => $uid,
                'group_id' => $groupId,
                'count'    => $qty
            ]);
        }
    }

    /**
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getBadgeByUid($uid)
    {
        if ($uid) {
            return $badge = $this->model->newQuery()->where('user_id', '=', $uid)->get(['group_id', 'count']);
        }
    }

    /**
     * @param $uid
     * @param $groupId
     */
    public function resetBadge($uid, $groupId)
    {
        $this->model->newQuery()->where('user_id', '=', $uid)->where('group_id', '=', $groupId)->update([
            'count' => 0
        ]);
    }

    public function setBadgeCount($uid, $groupId, $count)
    {
        if ($this->issetBadge($uid, $groupId)) {
            $this->model->newQuery()->where('user_id', '=', $uid)->where('group_id', '=', $groupId)->update([
                'count' => (int)$count
            ]);
        } else {
            $this->upBadge($uid, $groupId, $count);
        }
    }

    public function issetBadge($uid, $groupId)
    {
        return $this->model->newQuery()->where('user_id', '=', $uid)->where('group_id', '=', $groupId)->exists();
    }
}