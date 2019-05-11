<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatUsers;
use App\Repositories\EloquentRepository;

class ChatUsersRepository extends EloquentRepository
{

    public function __construct(ChatUsers $model)
    {
        $this->model = $model;
    }

    public function becomeFriends($uid, $fid)
    {
        $bool = false;
        if ($uid && $fid) {
            $bool = $this->model->newQuery()->insert([
                'user_id_1' => $uid < $fid ? $uid : $fid,
                'user_id_2' => $uid > $fid ? $uid : $fid,
                'status'    => 0,
            ]);
        }
        return $bool;
    }

    public function isFriends($uid, $fid)
    {
        if ($uid < $fid) {
            return $this->model->newQuery()->where('user_id_1', '=', $uid)->where('user_id_2', '=', $fid)->exists();
        } else {
            return $this->model->newQuery()->where('user_id_1', '=', $fid)->where('user_id_2', '=', $uid)->exists();
        }
    }
}