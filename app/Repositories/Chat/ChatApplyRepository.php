<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatApply;
use App\Repositories\EloquentRepository;

class ChatApplyRepository extends EloquentRepository
{

    public function __construct(ChatApply $model)
    {
        $this->model = $model;
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
}