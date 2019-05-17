<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatGroup;
use App\Repositories\EloquentRepository;

class ChatGroupRepository  extends EloquentRepository
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
        return $this->model->newQuery()->with('groupMembers')->where('group_id','=', $groupId)->get();
    }
}