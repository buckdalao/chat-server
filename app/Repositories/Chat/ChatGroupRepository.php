<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatGroup;
use App\Repositories\EloquentRepository;

class ChatGroupRepository extends EloquentRepository
{

    public function __construct(ChatGroup $model)
    {
        $this->model = $model;
    }

    /**
     * 获取群里的用户
     *
     * @param $groupId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getGroupUser($groupId)
    {
        return $this->model->newQuery()->with('groupMembers')->where('group_id', '=', $groupId)->get();
    }

    /**
     * 创建群
     *
     * @param $userId
     * @param $groupName
     * @return int
     */
    public function createGroup($userId, $groupName)
    {
        $groupId = 0;
        if ($userId && $groupName) {
            $groupId = $this->model->newQuery()->insertGetId([
                'group_name' => $groupName,
                'user_id'    => $userId,
                'photo'      => 'storage/photos/group_photo.jpg' // default
            ]);
            $this->model->newQuery()->whereKey($groupId)->update(['group_number' => 100000 + $groupId]);
        }
        return $groupId;
    }

    /**
     * @param $cn
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getGroupByNo($cn)
    {
        return $this->model->newQuery()->where('group_number', '=', $cn)->first();
    }
}