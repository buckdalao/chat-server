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

    /**
     * 建立好友关系
     *
     * @param $uid
     * @param $fid
     * @return bool
     */
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

    /**
     * 检测是否是好友
     *
     * @param $uid
     * @param $fid
     * @return bool
     */
    public function isFriends($uid, $fid)
    {
        if ($uid < $fid) {
            return $this->model->newQuery()->where('user_id_1', '=', $uid)->where('user_id_2', '=', $fid)->exists();
        } else {
            return $this->model->newQuery()->where('user_id_1', '=', $fid)->where('user_id_2', '=', $uid)->exists();
        }
    }

    /**
     * 获取好友关联信息
     *
     * @param $uid
     * @param $fid
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getChat($uid, $fid)
    {
        if ($uid < $fid) {
            return $this->model->newQuery()->where('user_id_1', '=', $uid)->where('user_id_2', '=', $fid)->first();
        } else {
            return $this->model->newQuery()->where('user_id_1', '=', $fid)->where('user_id_2', '=', $uid)->first();
        }
    }
}