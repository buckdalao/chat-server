<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatApplyRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatUsersRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatApplyController extends Controller
{
    protected $chatGroupUserRepository;
    protected $chatApplyRepository;
    protected $chatUsersRepository;

    public function __construct(ChatGroupUserRepository $chatGroupUserRepository,
                                ChatApplyRepository $applyRepository,
                                ChatUsersRepository $chatUsersRepository)
    {
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->chatApplyRepository = $applyRepository;
        $this->chatUsersRepository = $chatUsersRepository;

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
            return $this->badRequest('Parameter error');
        }
        $res = $this->chatGroupUserRepository->joinGroup($request->user(), $request->get('group_id'));
        return $res ? $this->success('加入成功') : $this->fail('加入失败');
    }

    public function addFriends(Request $request)
    {
        if (empty($request->user()->id) || $this->requestIsEmpty($request, ['friend_id', 'group_id'], 'or')){
            return $this->badRequest('Parameter error');
        }
        $id = $request->get('friend_id') ?: $request->get('group_id');
        $isGroup = $request->get('group_id') ? true : false;
        if (!$isGroup && $this->chatUsersRepository->isFriends($request->user()->id, $id)) {
            return $this->fail('已是好友');
        }
        if ($isGroup && $this->chatGroupUserRepository->isInGroup($request->user()->id, $id)){
            return $this->fail('已在群中');
        }
        if ($this->chatApplyRepository->verify($id, $isGroup, $request->user()->id)) {
            return $this->fail('已申请过');
        }
        $this->chatApplyRepository->createApply([
            'apply_user_id' => $request->user()->id,
            'friend_id'     => $request->get('friend_id'),
            'group_id'      => $request->get('group_id'),
            'remarks'       => $request->get('remarks'),
        ]);
        return $this->success('申请已发送');
    }

    public function audit(Request $request, $applyId)
    {
        if (empty($request->user()->id) || empty($applyId) || $this->requestIsEmpty($request, ['audit'])){
            return $this->badRequest('Parameter error');
        }
        $id = $this->chatApplyRepository->auditApply($applyId, $request->get('audit'));
        return $this->successWithData($id);
    }
}
