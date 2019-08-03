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
     * 通过 chat number 获取群信息
     *
     * @param $cn
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getGroupByNo($cn)
    {
        return $this->model->newQuery()->where('group_number', '=', $cn)->first();
    }

    /**
     * 获取群的群主uid
     *
     * @param $groupId
     * @return int|mixed
     */
    public function getGroupOwnerUid($groupId)
    {
        $uid = 0;
        $res = $this->model->newQuery()->whereKey($groupId)->first(['user_id']);
        if ($res) {
            $uid = $res->user_id;
        }
        return $uid;
    }

    /**
     * 通过group_id 获取群信息
     *
     * @param $groupId
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getGroupByGroupId($groupId)
    {
        return $this->model->newQuery()->whereKey($groupId)->first();
    }

    /**
     * 获取所有群列表
     *
     * @param null $keyword
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function allGroup($keyword = null, $limit = 15)
    {
        if ($keyword) {
            return $this->model->newQuery()->where(function ($query) use ($keyword) {
                $query->where('group_name', 'like', "%{$keyword}%")->orWhere('group_number', '=', (int)$keyword);
            })->paginate($limit ?? 15);
        }
        return $this->model->newQuery()->paginate($limit ?? 15);
    }
}