<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatGroupController extends Controller
{
    protected $chatGroupRepository;

    public function __construct(ChatGroupRepository $chatGroupRepository)
    {
        $this->chatGroupRepository = $chatGroupRepository;
    }

    /**
     * 获取群成员
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupMember($groupId)
    {
        if (empty($groupId)) {
            return $this->badRequest('Parameter error');
        }
        $members = $this->chatGroupRepository->getGroupUser($groupId);
        return $this->successWithData($members);
    }

    /**
     * 创建群
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGroup(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('group_name'))) {
            return $this->badRequest();
        }
        $groupId = $this->chatGroupRepository->createGroup($request->user()->id, $request->get('group_name'));
        return $this->successWithData(['group_id' => $groupId]);
    }
}
