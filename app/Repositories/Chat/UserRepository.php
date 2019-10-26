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
            ->where('status', '!=', 1)
            ->where(function ($query) use ($userId) {
                $query->where('user_id_1', '=', (int)$userId)->orWhere('user_id_2', '=', (int)$userId);
            })->get();
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
     * 通过uid 获取用户信息
     *
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getUserById($uid)
    {
        return $this->model->newQuery()->whereKey($uid)->first();
    }

    /**
     * 通过chat_number获取用户信息
     *
     * @param $cn
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getUserByNo($cn)
    {
        return $this->model->newQuery()->where('chat_number', '=', $cn)->first();
    }

    /**
     * 通过email获取客户信息
     *
     * @param $email
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getUserByEmail($email)
    {
        return $this->model->newQuery()->where('email', '=', $email)->first();
    }

    /**
     * 通过phone获取客户信息
     *
     * @param $phone
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getUserByPhone($phone)
    {
        return $this->model->newQuery()->where('phone', '=', $phone)->first();
    }

    /**
     * 获取所有用户列表
     *
     * @param null $keyword  搜索关键字
     * @param int  $limit  每页显示条目
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function allUser($keyword = null, $limit = 15)
    {
        if ($keyword) {
            return $this->model->newQuery()->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "{$keyword}%")->orWhere('email', 'like', "{$keyword}%")->orWhere('chat_number', '=', (int)$keyword);
            })->paginate($limit ?? 15);
        }
        return $this->model->newQuery()->paginate($limit ?? 15);
    }
}