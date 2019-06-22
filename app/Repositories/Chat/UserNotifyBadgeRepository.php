<?php

namespace App\Repositories\Chat;

use App\Models\Chat\UserNotifyBadge;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class UserNotifyBadgeRepository  extends EloquentRepository
{

    public function __construct(UserNotifyBadge $model)
    {
        $this->model = $model;
    }

    public function setBadge($uid, $type = 0)
    {
        if (empty($uid)) {
            return ;
        }
        $badge = $this->model->newQuery()->where('user_id', '=', $uid)->where('type', '=', $type)->first(['id', 'count']);
        if ($badge && $badge->id) {
            $this->model->newQuery()->whereKey($badge->id)->update([
                'count' => DB::raw('count + 1')
            ]);
        } else {
            $this->model->newQuery()->insert([
                'user_id' => $uid,
                'type' => $type,
                'count' => 1
            ]);
        }
    }

    public function resetBadge($uid, $type = 0)
    {
        $this->model->newQuery()->where('user_id', '=', $uid)->where('type', '=', $type)->update([
            'count' => 0
        ]);
    }

    /**
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getBadgeByUid($uid, $type = 0)
    {
        if ($uid) {
            return $badge = $this->model->newQuery()->where('user_id', '=', $uid)
                ->where('type', '=', $type)->first(['type', 'count']);
        }
    }
}