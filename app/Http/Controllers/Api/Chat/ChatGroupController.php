<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatGroupController extends Controller
{
    protected $chatGroupRepository;
    protected $chatGroupUserRepository;

    public function __construct(ChatGroupRepository $chatGroupRepository, ChatGroupUserRepository $chatGroupUserRepository)
    {
        $this->chatGroupRepository = $chatGroupRepository;
        $this->chatGroupUserRepository = $chatGroupUserRepository;
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
            return $this->badRequest(__('parameter error'));
        }
        $members = $this->chatGroupUserRepository->groupUserInfoList($groupId)->toArray();
        $groupInfo = $this->chatGroupRepository->getGroupByGroupId($groupId)->toArray();
        if ($groupInfo['photo']) {
            $groupInfo['photo'] = asset($groupInfo['photo']);
        }
        foreach ($members as $v) {
            if ($groupInfo['user_id'] == $v['user_id']) {
                $groupInfo['group_owner_name'] = $v['group_user_name'] ?: $v['name'];
                break;
            }
        }
        $groupInfo['group_members'] = $members;
        return $this->successWithData($groupInfo);
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
