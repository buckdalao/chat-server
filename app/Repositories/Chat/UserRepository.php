<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatGroup;
use App\Models\Chat\ChatUsers;
use App\Models\Chat\User;
use App\Repositories\EloquentRepository;
use GatewayClient\Gateway;

class UserRepository extends EloquentRepository
{
    protected $chatUserModel;

    protected $chatGroupModel;

    public function __construct(User $model, ChatUsers $chatUsers, ChatGroup $chatGroup)
    {
        $this->model = $model;
        $this->chatUserModel = $chatUsers;
        $this->chatGroupModel = $chatGroup;
        Gateway::$registerAddress = env('REGISTER_SERVER');
    }

    /**
     * DB返回的好友list 带user_id
     *
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function friendsList($userId)
    {
        return $this->chatUserModel->newQuery()
            ->where('user_id_1', '=', (int)$userId)->orWhere('user_id_2', '=', (int)$userId)->get();
    }

    /**
     * 详细好友列表
     *
     * @param $userId
     * @return array
     */
    public function friendsListDetailed($userId)
    {
        $list = $this->friendsList($userId);
        $listDetail = [];
        if ($list) {
            foreach ($list as $v) {
                $uid = $v->user_id_1 == $userId ? $v->user_id_2 : $v->user_id_1;
                $user = $this->model->newQuery()->find($uid, ['id', 'email', 'name', 'phone', 'photo']);
                if ($user) {
                    $users = $user->toArray();
                    $users['chat_id'] = $v->id;
                    $users['photo'] = asset($users['photo']);
                    $users['is_online'] = Gateway::isUidOnline($uid);
                    $listDetail[] = $users;
                }
            }
        }
        return $listDetail;
    }

    /**
     * 获取用户的所在群信息
     *
     * @param $userId
     * @return array
     */
    public function groupList($userId)
    {
        // $this->model->newQuery()->with('groupUser')->where('id','=', $userId)->get();
        $users = $this->model->newQuery()->find($userId);
        $list = $users->groupUser;
        $groupList = [];
        if ($list) {
            foreach ($list as $v) {
                $group = $this->chatGroupModel->newQuery()->find((int)$v->group_id, ['group_name', 'group_status', 'photo']);
                $groupList[] = [
                    'group_user_id'   => $v->group_user_id,
                    'group_id'        => $v->group_id,
                    'user_id'         => $v->user_id,
                    'group_user_name' => $v->group_user_name,
                    'status'          => $v->status,
                    'created_at'      => $v->created_at,
                    'group_name'      => $group->group_name,
                    'group_status'    => $group->group_status,
                    'photo'           => asset($group->photo),
                ];
            }
        }
        return $groupList;
    }

    /**
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getUserById($uid)
    {
        return $this->model->newQuery()->whereKey($uid)->first();
    }
}