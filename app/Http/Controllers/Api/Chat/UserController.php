<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    /**
     * 获取好友列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFriendsList(Request $request)
    {
        if (empty($request->user()->id)){
            return $this->badRequest('Parameter error');
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
        if (empty($request->user()->id)){
            return $this->badRequest();
        }
        $res = $this->userRepository->groupList($request->user()->id);
        return $this->successWithData($res);
    }
}
