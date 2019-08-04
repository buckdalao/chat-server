<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $userRepository;

    protected $chatUsersRepository;

    public function __construct(UserRepository $repository, ChatUsersRepository $chatUsersRepository)
    {
        parent::__construct();
        $this->userRepository = $repository;
        $this->chatUsersRepository = $chatUsersRepository;
    }

    /**
     * 获取好友列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFriendsList(Request $request)
    {
        if (empty($request->user()->id)) {
            return $this->badRequest(__('parameter error'));
        }
        $res = $this->userRepository->friendsListDetailed($request->user()->id);
        return $this->successWithData($res);
    }

    /**
     * 获取用户所在群
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupList(Request $request)
    {
        if (empty($request->user()->id)) {
            return $this->badRequest();
        }
        $res = $this->userRepository->groupList($request->user()->id);
        return $this->successWithData($res);
    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     * @param         $uid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo(Request $request, $uid)
    {
        if (empty($request->user()->id) || empty($uid) || $request->user()->id == $uid) {
            return $this->badRequest();
        }
        $user = $this->userRepository->getUserById($uid);
        if ($user) {
            $user->photo = asset($user->photo);
        }
        $isFriend = $this->chatUsersRepository->isFriends($request->user()->id, $uid);
        return $this->successWithData([
            'user_info' => $user->toArray(),
            'is_friend' => $isFriend
        ]);
    }

    /**
     * 用户列表 带分页信息 支持keyword参数模糊查询
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers(Request $request)
    {
        return $this->successWithData($this->userRepository->allUser($request->get('keyword')));
    }
}
