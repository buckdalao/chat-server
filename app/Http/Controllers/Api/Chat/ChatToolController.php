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
        $fails = Validator::make($request->all(), [
            'chat_number' => ['required', 'numeric', 'min:100000', 'max:9999999']
        ], [
            'required' => 'The :attribute field is required.',
            'numeric'    => 'The :attribute must be numeric.',
        ])->errors()->getMessages();
        if ($fails) {
            return $this->fail($fails['chat_number'][0]);
        }
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
            return $this->fail('号码错误');
        }
    }
}
