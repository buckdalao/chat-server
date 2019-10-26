<?php

namespace App\Http\Controllers\Api\Chat;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Models\Chat\User;
use App\Repositories\Chat\ChatApplyRepository;
use App\Repositories\Chat\ChatGroupRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserNotifyBadgeRepository;
use App\Repositories\Chat\UserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatApplyController extends Controller
{
    use WsMessageTrait, BaseChatTrait;
    protected $chatGroupUserRepository;
    protected $chatApplyRepository;
    protected $chatUsersRepository;
    protected $chatGroupRepository;
    protected $userNotifyBadgeRepository;
    protected $userRepository;

    public function __construct(ChatGroupUserRepository $chatGroupUserRepository,
                                ChatApplyRepository $applyRepository,
                                ChatUsersRepository $chatUsersRepository,
                                ChatGroupRepository $chatGroupRepository,
                                UserNotifyBadgeRepository $userNotifyBadgeRepository,
                                UserRepository $userRepository)
    {
        Gateway::$registerAddress = getenv('REGISTER_SERVER');
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->chatApplyRepository = $applyRepository;
        $this->chatUsersRepository = $chatUsersRepository;
        $this->chatGroupRepository = $chatGroupRepository;
        $this->userNotifyBadgeRepository = $userNotifyBadgeRepository;
        $this->userRepository = $userRepository;
        parent::__construct();
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
            return $this->badRequest(__('parameter error'));
        }
        $res = $this->chatGroupUserRepository->joinGroup($request->user(), $request->get('group_id'));
        return $res ? $this->success(__('join success')) : $this->fail(__('join fail'));
    }

    public function addFriends(Request $request)
    {
        $id = $request->get('friend_id') ?: $request->get('group_id');
        if (empty($request->user()->id) || empty($id)){
            return $this->badRequest(__('parameter error'));
        }
        $isGroup = $request->get('group_id') ? true : false;
        if (!$isGroup && $this->chatUsersRepository->isFriends($request->user()->id, $id)) {
            return $this->fail(__('is already a friendship'));
        }
        if ($isGroup && $this->chatGroupUserRepository->isInGroup($request->user()->id, $id)){
            return $this->fail(__('is already the group'));
        }
        if ($this->chatApplyRepository->verify($id, $isGroup, $request->user()->id)) {
            return $this->fail(__('application has been sent'));
        }
        $remark = $request->get('remarks');
        if (empty($remark)) {
            $remark = $isGroup ? '申请加入群' : '申请添加好友';
        }
        $this->chatApplyRepository->createApply([
            'apply_user_id' => $request->user()->id,
            'friend_id'     => $request->get('friend_id'),
            'group_id'      => $request->get('group_id'),
            'remarks'       => $remark,
        ]);
        if ($isGroup) {
            $uid = $this->chatGroupRepository->getGroupOwnerUid($request->get('group_id'));
            if ($uid) {
                if (Gateway::isUidOnline($uid)) {
                    Gateway::sendToUid($uid, $this->message($request, [
                        'type' => $this->getType('apply_notify'),
                        'data' => 'apply join group'
                    ]));
                } else {
                    $this->userNotifyBadgeRepository->setBadge($uid);
                }
            }
        } else {
            if (Gateway::isUidOnline($request->get('friend_id'))) {
                Gateway::sendToUid($request->get('friend_id'), $this->message($request, [
                    'type' => $this->getType('apply_notify'),
                    'data' => 'apply add friend'
                ]));
            } else {
                $this->userNotifyBadgeRepository->setBadge($request->get('friend_id'));
            }
        }
        return $this->success(__('application sent'));
    }

    /**
     * 好友或加群申请审核
     *
     * @param Request $request
     * @param         $applyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request, $applyId)
    {
        if (empty($request->user()->id) || empty($applyId) || $request->is('audit')){
            return $this->badRequest(__('parameter error'));
        }
        if ($this->chatApplyRepository->hasBeenAudit($applyId)) {
            return $this->fail(__('the approved'));
        }
        $this->chatApplyRepository->auditApply($applyId, $request->get('audit'));
        $applyInfo = $this->chatApplyRepository->getApplyInfoById($applyId);
        $friendsList = [];
        if ($applyInfo && $request->get('audit') == 1) { // 同意申请
            if ($applyInfo->group_id) {
                $user = User::find($applyInfo->apply_user_id);
                $this->chatGroupUserRepository->joinGroup($user, $applyInfo->group_id);
            } else {
                $this->chatUsersRepository->becomeFriends($applyInfo->apply_user_id, $applyInfo->friend_id);
                $friendsList = $this->userRepository->friendsListDetailed($request->user()->id);
            }
            // 如果申请人在线  提醒申请人更新好友列表 或者群列表
            if (Gateway::isUidOnline($applyInfo->apply_user_id)) {
                Gateway::sendToUid($applyInfo->apply_user_id, $this->message($request, [
                    'type' => $this->getType('release_friend_list'),
                    'data' => $applyInfo->group_id ? 1 : 0
                ]));
            }
            if ($friendsList) {
                return $this->successWithData([
                    'friend_list'  => $friendsList,
                ]);
            }
        }
        return $this->success();
    }

    public function getApplyList(Request $request)
    {
        $uid = $request->user()->id;
        $applyNotify = $this->chatApplyRepository->getNotifyByUid($uid);
        $this->userNotifyBadgeRepository->resetBadge($uid);
        return $this->successWithData($applyNotify);
    }

    public function resetNotifyBadge(Request $request)
    {
        $uid = $request->user()->id;
        $this->userNotifyBadgeRepository->resetBadge($uid, 0);
        return $this->success();
    }

    /**
     *  拉人进群
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inviteToGroup(Request $request)
    {
        Validator::make($request->all(), [
            'users' => 'required',
            'group_id' => 'required|integer'
        ])->validate();
        $users = $request->get('users');
        $groupId = $request->get('group_id');
        if (is_array($users)) {
            foreach ($users as $uid) {
                if ($this->chatGroupUserRepository->isInGroup($uid, $groupId) || $this->chatApplyRepository->verify($groupId, true, $uid)){
                    break;
                }
                $this->chatApplyRepository->createApply([
                    'apply_user_id' => $uid,
                    'friend_id'     => $request->user()->id,
                    'group_id'      => $groupId,
                    'remarks'       => $request->get('remarks') ?: '邀请加入该群',
                ]);
                if (Gateway::isUidOnline($uid)) {
                    Gateway::sendToUid($uid, $this->message($request, [
                        'type' => $this->getType('apply_notify'),
                        'data' => $request->get('remarks') ?: '邀请加入该群'
                    ]));
                } else {
                    $this->userNotifyBadgeRepository->setBadge($uid);
                }
            }
        }
        return $this->success(__('application sent'));
    }
}
