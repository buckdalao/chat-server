<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatApplyRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatApplyController extends Controller
{
    protected $chatGroupUserRepository;

    protected $chatApplyRepository;

    public function __construct(ChatGroupUserRepository $chatGroupUserRepository,
                                ChatApplyRepository $applyRepository)
    {
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->chatApplyRepository = $applyRepository;
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
        if (empty($request->user()->id) || $this->requestIsEmpty($request, ['friend_id', 'group_id'])){
            return $this->badRequest('Parameter error');
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
