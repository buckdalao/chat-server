<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatUsersRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatUsersController extends Controller
{
    protected $chatUserRepository;

    public function __construct(ChatUsersRepository $chatUsersRepository)
    {
        $this->chatUserRepository = $chatUsersRepository;
    }

    /**
     * 是否已建立好友
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function isFriends(Request $request, $friendId)
    {
        if (empty($request->user()->id) || empty($friendId) || $request->user()->id == $friendId) {
            return $this->badRequest(__('parameter error'));
        }
        $res = $this->chatUserRepository->isFriends($request->user()->id, $friendId);
        return $this->successWithData($res);
    }

    /**
     * 添加好友
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function becomeFriends(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('friend_id'))){
            return $this->badRequest(__('parameter error'));
        }
        if ($this->chatUserRepository->isFriends($request->user()->id, $request->get('friend_id')) == false) {
            $res = $this->chatUserRepository->becomeFriends($request->user()->id, $request->get('friend_id'));
            return $this->successWithData($res);
        }else {
            return $this->badRequest();
        }
    }
}
