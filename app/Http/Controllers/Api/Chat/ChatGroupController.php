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
    public function getGroupMember(Request $request)
    {
        if (empty($request->get('group_id'))) {
            return $this->badRequest('Parameter error');
        }
        $members = $this->chatGroupRepository->getGroupUser($request->get('group_id'));
        return $this->successWithData($members);
    }
}
