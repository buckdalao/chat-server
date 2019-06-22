<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatApply;
use App\Models\Chat\ChatGroup;
use App\Models\Chat\User;
use App\Repositories\EloquentRepository;

class ChatApplyRepository extends EloquentRepository
{

    protected $user;
    protected $group;

    public function __construct(ChatApply $model, User $user, ChatGroup $group)
    {
        $this->model = $model;
        $this->user = $user;
        $this->group = $group;
    }

    /**
     * 创建申请
     *
     * @param array $data
     * @return int
     */
    public function createApply(array $data)
    {
        $insertID = 0;
        if (sizeof($data)) {
            $data['apply_time'] = time();
            $insertID = $this->model->newQuery()->insertGetId([
                'apply_user_id' => $data['apply_user_id'],
                'friend_id'     => $data['friend_id'] ?: 0,
                'group_id'      => $data['group_id'] ?: 0,
                'remarks'       => $data['remarks'] ?: '',
                'apply_status'  => 0,
                'apply_time'    => time(),
            ]);
        }
        return $insertID;
    }

    /**
     * @param $id
     * @param $audit
     * @return int
     */
    public function auditApply($id, $audit)
    {
        $res = 0;
        if ($id) {
            $res = $this->model->newQuery()->whereKey($id)->update([
                'apply_status' => (int)$audit,
                'audit_time' => time()
            ]);
        }
        return $res;
    }

    /**
     * @param $id
     * @param $isGroup
     * @param $uid
     * @return bool
     */
    public function verify($id, $isGroup, $uid)
    {
        if ($isGroup) {
            return $this->model->newQuery()->where('group_id', '=', $id)->where('apply_user_id', '=', $uid)
                ->where('apply_status', '=', 0)->exists();
        } else {
            return $this->model->newQuery()->where('friend_id', '=', $id)->where('apply_user_id', '=', $uid)
                ->where('apply_status', '=', 0)->exists();
        }
    }

    public function getNotifyByUid($uid)
    {
        $users = $this->model->newQuery()->from($this->model->alias('ca'))
            ->leftJoin($this->user->alias('u'), 'ca.apply_user_id', '=', 'u.id')
            ->leftJoin($this->group->alias('g'), 'ca.group_id', '=', 'g.group_id')
            ->where('ca.friend_id', '=', $uid)->orWhere('g.user_id', '=', $uid)
            ->orderBy('ca.id', 'asc')
            ->get([
                'u.id as uid', 'u.name as user_name', 'ca.apply_time', 'ca.remarks', 'u.photo', 'u.chat_number', 'ca.group_id', 'ca.id', 'ca.apply_status'
                ]);
        if ($users) {
            collect($users)->map(function ($item) {
                if ($item->photo) {
                    $item->photo = asset($item->photo);
                }
            });
        }
        return $users;
    }

    public function getApplyInfoById($applyId)
    {
        return $this->model->newQuery()->whereKey($applyId)->first();
    }

    /**
     * 是否已审核
     *
     * @param $applyId
     * @return bool
     */
    public function hasBeenAudit($applyId)
    {
        return $this->model->newQuery()->whereKey($applyId)->where('apply_status', '>', 0)->exists();
    }
}