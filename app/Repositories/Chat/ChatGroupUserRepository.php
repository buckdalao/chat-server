<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatGroup;
use App\Models\Chat\ChatGroupUser;
use App\Repositories\EloquentRepository;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ChatGroupUserRepository extends EloquentRepository
{
    protected $userModel;

    protected $groupModel;

    public function __construct(ChatGroupUser $model, User $user, ChatGroup $chatGroup)
    {
        $this->model = $model;
        $this->userModel = $user;
        $this->groupModel = $chatGroup;
    }

    /**
     * 用户加入群组
     *
     * @param Model $userInfo
     * @param       $groupId
     * @return bool
     */
    public function joinGroup(Model $userInfo, $groupId)
    {
        $groupInfo = $this->groupModel->where('group_id', '=', (int)$groupId)->first();
        $bool = false;
        if ($userInfo->id && $groupInfo->group_id) {
            $verifyGroup = $this->isInGroup($userInfo->id, $groupId);
            if (!$verifyGroup) {
                $this->model->create([
                    'user_id' => $userInfo->id,
                    'group_id'=> $groupId,
                    'group_user_name' => $userInfo->name
                ]);
                $bool = true;
            }
        }
        return $bool;
    }

    /**
     * @param $uid
     * @param $groupId
     * @return bool
     */
    public function isInGroup($uid, $groupId)
    {
        return $this->model->newQuery()->where('user_id', '=', $uid)->where('group_id', '=', $groupId)->exists();
    }

    /**
     * @param $groupId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getGroupUserList($groupId)
    {
        return $this->model->newQuery()->where('group_id', '=', $groupId)->get();
    }

    /**
     * @param $groupId
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getGroupUserInfo($groupId, $uid)
    {
        return $this->model->newQuery()->where('group_id', '=', $groupId)->where('user_id', '=', $uid)->first();
    }
}