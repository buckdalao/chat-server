<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatToolController extends Controller
{
    protected $chatGroupUserRepository;
    protected $chatGroupRepository;
    protected $chatUsersRepository;
    protected $userRepository;

    public function __construct(ChatGroupUserRepository $chatGroupUserRepository,
                                ChatGroupRepository $chatGroupRepository,
                                UserRepository $userRepository,
                                ChatUsersRepository $chatUsersRepository)
    {
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->userRepository = $userRepository;
        $this->chatUsersRepository = $chatUsersRepository;
        $this->chatGroupRepository = $chatGroupRepository;
    }

    public function searchNo(Request $request)
    {
        Validator::make($request->all(), [
            'chat_number' => ['required', 'numeric', 'min:100000', 'max:9999999']
        ])->validate();
        $cn = $request->get('chat_number');
        $uid = $request->user()->id;
        $data = $this->userRepository->getUserByNo($cn);
        $isGroup = false;
        if (empty($data)) {
            $data = $this->chatGroupRepository->getGroupByNo($cn);
            $isGroup = true;
        }
        if ($data) {
            $isRelated = $isGroup ? $this->chatGroupUserRepository->isInGroup($uid, $data->group_id) : $this->chatUsersRepository->isFriends($uid, $data->id);
            $data->photo = asset($data->photo);
            $res = $data->toArray();
            $res['is_related'] = $isRelated;
            $res['is_self'] = !$isGroup && $data->id == $uid ? true : false;
            $res['is_group'] = $isGroup;
            return $this->successWithData($res);
        } else {
            return $this->fail(__('number is wrong'));
        }
    }
}
