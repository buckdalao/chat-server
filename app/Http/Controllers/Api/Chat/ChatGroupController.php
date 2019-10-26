<?php

namespace App\Http\Controllers\Api\Chat;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Repositories\Chat\ChatGroupRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatGroupController extends Controller
{
    use WsMessageTrait, BaseChatTrait;
    protected $chatGroupRepository;
    protected $chatGroupUserRepository;

    public function __construct(ChatGroupRepository $chatGroupRepository, ChatGroupUserRepository $chatGroupUserRepository)
    {
        parent::__construct();
        $this->chatGroupRepository = $chatGroupRepository;
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        Gateway::$registerAddress = getenv('REGISTER_SERVER');
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
        $currentUser = $this->chatGroupUserRepository->getGroupUserInfo($groupId, \request()->user()->id);
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
        $groupInfo['current_user'] = $currentUser;
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

    /**
     * 获取所有群
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllGroupList(Request $request)
    {
        return $this->successWithData($this->chatGroupRepository->allGroup($request->get('keyword')));
    }

    /**
     * 获取群里某个用户信息
     *
     * @param $groupId
     * @param $uid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupUserInfo($groupId, $uid)
    {
        $groupUser = $this->chatGroupUserRepository->getGroupUserInfo($groupId, $uid);
        return $this->successWithData($groupUser);
    }

    /**
     * 退出群
     *
     * @param $request
     * @param $groupId
     * @param $uid
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitTheGroup(Request $request, $groupId, $uid)
    {
        $owner = $this->chatGroupRepository->getGroupOwnerUid($groupId);
        if ($owner == $uid) {
            return $this->fail('Unauthorized', 401);
        }
        $this->chatGroupUserRepository->removeGroupUser($groupId, $uid);
        if (Gateway::isUidOnline($uid)) {
            Gateway::sendToUid($uid, $this->message($request, [
                'type' => $this->getType('release_friend_list'),
                'data' => 1
            ]));
        }
        return $this->success();
    }

    /**
     * 修改群昵称
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editGroupUserName(Request $request)
    {
        Validator::make($request->all(), [
            'group_id' => 'required|integer',
            'name' => 'required|string'
        ])->validate();
        $groupId = $request->get('group_id');
        $name = $request->get('name');
        $uid = $request->user()->id;
        $this->chatGroupUserRepository->editGroupUserName($groupId, $uid, $name);
        return $this->success();
    }

    /**
     * 修改群名称
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editGroupName(Request $request)
    {
        Validator::make($request->all(), [
            'group_id' => 'required|integer',
            'name' => 'required|string'
        ])->validate();
        $groupId = $request->get('group_id');
        $name = $request->get('name');
        $uid = $request->user()->id;
        $owner = $this->chatGroupRepository->getGroupOwnerUid($groupId);
        if ($uid != $owner) {
            return $this->fail('Unauthorized', 401);
        }
        $this->chatGroupRepository->editGroupName($groupId, $name);
        Gateway::sendToGroup($groupId, $this->message($request, [
            'type' => $this->getType('release_friend_list'),
            'data' => 1
        ]));
        return $this->success();
    }
}
