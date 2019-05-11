<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatUsers;
use App\Models\Chat\User;
use App\Repositories\EloquentRepository;

class UserRepository  extends EloquentRepository
{
    protected $chatUserModel;

    public function __construct(User $model, ChatUsers $chatUsers)
    {
        $this->model = $model;
        $this->chatUserModel = $chatUsers;
    }

    public function friendsList($userId)
    {
        return $this->chatUserModel->newQuery()
            ->where('user_id_1','=', (int)$userId)->orWhere('user_id_2','=', (int)$userId)->get();
    }

    public function friendsListDetailed($userId)
    {
        $list = $this->friendsList($userId);
        $listDetail = [];
        if ($list){
            foreach ($list as $v){
                $uid = $v->user_id_1 == $userId ? $v->user_id_2 : $v->user_id_1;
                $user = $this->model->newQuery()->find($uid, ['email', 'name', 'phone']);
                if ($user) {
                    $listDetail[] = $user->toArray();
                }
            }
        }
        return $listDetail;
    }
}