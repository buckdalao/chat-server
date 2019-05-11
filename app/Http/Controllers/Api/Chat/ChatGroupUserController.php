<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupUserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatGroupUserController extends Controller
{
    protected $chatGroupUserRepository;

    public function __construct(ChatGroupUserRepository $chatGroupUserRepository)
    {
        $this->chatGroupUserRepository = $chatGroupUserRepository;
    }

    /**
     * 登录用户加入某个群
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinGroup(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('group_id'))){
            return $this->fail('Parameter error');
        }
        $res = $this->chatGroupUserRepository->joinGroup($request->user(), $request->get('group_id'));
        return $res ? $this->success('加入成功') : $this->fail('加入失败');
    }
}
