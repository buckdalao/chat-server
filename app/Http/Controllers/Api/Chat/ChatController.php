<?php

namespace App\Http\Controllers\Api\Chat;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Libs\Upload\UploadFactory;
use App\Repositories\Chat\ChatApplyRepository;
use App\Repositories\Chat\ChatGroupMessageBadgeRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatMessageBadgeRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserNotifyBadgeRepository;
use App\Repositories\Chat\UserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    use WsMessageTrait, BaseChatTrait;

    /**
     * @var ChatUsersRepository
     */
    protected $chatUsersRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ChatMessageBadgeRepository
     */
    protected $chatMessageBadgeRepository;

    /**
     * @var ChatGroupMessageBadgeRepository
     */
    protected $groupMessageBadgeRepository;

    /**
     * @var
     */
    protected $chatGroupUserRepository;

    protected $chatApplyRepository;
    protected $userNotifyBadgeRepository;

    public function __construct(ChatUsersRepository $chatUsersRepository, UserRepository $userRepository,
                                ChatMessageBadgeRepository $chatMessageBadgeRepository,
                                ChatGroupMessageBadgeRepository $groupMessageBadgeRepository,
                                ChatGroupUserRepository $chatGroupUserRepository,
                                ChatApplyRepository $chatApplyRepository,
                                UserNotifyBadgeRepository $userNotifyBadgeRepository)
    {
        Gateway::$registerAddress = env('REGISTER_SERVER');
        $this->chatUsersRepository = $chatUsersRepository;
        $this->userRepository = $userRepository;
        $this->chatMessageBadgeRepository = $chatMessageBadgeRepository;
        $this->groupMessageBadgeRepository = $groupMessageBadgeRepository;
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->chatApplyRepository = $chatApplyRepository;
        $this->userNotifyBadgeRepository = $userNotifyBadgeRepository;
        parent::__construct();
    }

    /**
     * 两人对话消息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function onChatMessage(Request $request)
    {
        Validator::make($request->all(), [
            'content' => 'required|string',
            'chat_id' => 'required|integer'
        ])->validate();
        $chatId = $request->get('chat_id');
        $content = $request->get('content');
        $base64Img = $request->get('base64_img');
        $videoCall = $request->get('video_call');
        $answerStatus = $request->get('answer_status');
        $uid = $request->user()->id;
        $fid = $this->chatUsersRepository->getFriendIdByChatId($uid, $chatId);
        if ($fid) {
            if ($base64Img) {
                foreach ($base64Img as $value) {
                    $saveInfo = UploadFactory::putBase64Str($value)->setDisk('public')->setPath('messages/img/' . dechex(rand(0, 15)) . dechex(rand(0, 15)))->save();
                    if ($saveInfo) {
                        $url = asset('storage/'.$saveInfo->savePath);
                        Gateway::sendToUid([$fid, $uid], $this->message($request, [
                            'type'    => $this->getType('img'),
                            'data'    => $url,
                            'chat_id' => $chatId
                        ]));
                        if (!Gateway::isUidOnline($fid)) { // 好友不在线做提醒
                            $this->chatMessageBadgeRepository->upBadge($fid, $chatId);
                        }
                        // 消息缓存
                        $this->setChatId($chatId)->setMessage([
                            'type'      => $this->getType('img'),
                            'data'      => 'storage/'.$saveInfo->savePath,
                            'uid'       => $uid,
                            'user_name' => $request->user()->name,
                            'photo'     => asset($request->user()->photo),
                        ])->saveRedis();
                    }
                }
                $content = preg_replace('/<img(\s*)[a-zA-Z0-9.+-="\'\s\/;:,_@]*>/i', '', $content);
                $content = strip_tags($content);
                if (empty($content) && $base64Img) {
                    return $this->success();
                }
            }
            if ($videoCall == 1) {
                $content .= Gateway::isUidOnline($fid) ? '' : ' failed';
            }
            $sendUser = $base64Img ? [$fid, $uid] : $fid;
            if ($videoCall != 2) { // 视频通话应当不走message通道
                Gateway::sendToUid($sendUser, $this->message($request, [
                    'type'    => $this->getType('message'),
                    'data'    => $content,
                    'chat_id' => $chatId
                ]));
                if (!Gateway::isUidOnline($fid)) { // 好友不在线做提醒
                    $this->chatMessageBadgeRepository->upBadge($fid, $chatId);
                }
                // 消息缓存
                $this->setChatId($chatId)->setMessage([
                    'type'      => $this->getType('message'),
                    'data'      => $content,
                    'uid'       => $uid,
                    'user_name' => $request->user()->name,
                    'photo'     => asset($request->user()->photo),
                ])->saveRedis();
            }
            if ($videoCall == 1) { // 视频通话请求
                if (!Gateway::isUidOnline($fid)) {
                    return $this->successWithData(['status' => 'failed', 'message' => '好友不在线']);
                }
                Gateway::sendToUid($fid, $this->message($request, [
                    'type'    => $this->getType('video_call'),
                    'data'    => $content,
                    'chat_id' => $chatId
                ]));
            }
            if ($videoCall == 2) { // 视频通话应当结果
                if (!Gateway::isUidOnline($fid)) {
                    return $this->successWithData(['status' => 'failed', 'message' => '好友不在线']);
                }
                Gateway::sendToUid($fid, $this->message($request, [
                    'type'    => $this->getType('video_answer'),
                    'data'    => $answerStatus,
                    'chat_id' => $chatId
                ]));
            }
        }
        return $this->success();
    }

    /**
     * 群内普通消息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function onGroupMessage(Request $request)
    {
        Validator::make($request->all(), [
            'content' => 'required|string',
            'group_id' => 'required|integer'
        ])->validate();
        $base64Img = $request->get('base64_img');
        $groupId = $request->get('group_id');
        $content = $request->get('content');
        $uid = $request->user()->id;
        $connectId = Gateway::getClientIdByUid($uid);
        $groupUser = $this->chatGroupUserRepository->getGroupUserInfo($groupId, $uid);
        $userName = $groupUser->group_user_name ? $groupUser->group_user_name : $request->user()->name;
        if ($base64Img) {
            foreach ($base64Img as $value) {
                $saveInfo = UploadFactory::putBase64Str($value)->setDisk('public')->setPath('messages/img/' . dechex(rand(0, 15)) . dechex(rand(0, 15)))->save();
                if ($saveInfo) {
                    $url = asset('storage/'.$saveInfo->savePath);
                    Gateway::sendToGroup($groupId, $this->message($request, [
                        'type'      => $this->getType('img'),
                        'data'      => $url,
                        'group_id'  => $groupId,
                        'user_name' => $userName
                    ]));
                    // 消息缓存
                    $this->setGroupId($groupId)->setMessage([
                        'type'      => $this->getType('img'),
                        'data'      => 'storage/'.$saveInfo->savePath,
                        'uid'       => $uid,
                        'user_name' => $userName,
                        'photo'     => asset($request->user()->photo),
                    ])->saveRedis();
                    $groupMembers = $this->chatGroupUserRepository->getGroupUserList($groupId);
                    if ($groupMembers) {
                        collect($groupMembers)->each(function ($member) {
                            if ($member->user_id && !Gateway::isUidOnline($member->user_id)) { // 群内不在线的用户做消息提醒
                                $this->groupMessageBadgeRepository->upBadge($member->user_id, $member->group_id);
                            }
                        });
                    }
                }
            }
            $content = preg_replace('/<img(\s*)[a-zA-Z0-9.+-="\'\s\/;:,_@]*>/i', '', $content);
            $content = strip_tags($content);
            if (empty($content) && $base64Img) {
                return $this->success();
            }
        }
        Gateway::sendToGroup($groupId, $this->message($request, [
            'type'      => $this->getType('message'),
            'data'      => $content,
            'group_id'  => $groupId,
            'user_name' => $userName
        ]), $base64Img ? null : $connectId);
        // 消息缓存
        $this->setGroupId($groupId)->setMessage([
            'type'      => $this->getType('message'),
            'data'      => $content,
            'uid'       => $uid,
            'user_name' => $userName,
            'photo'     => asset($request->user()->photo),
        ])->saveRedis();
        $groupMembers = $this->chatGroupUserRepository->getGroupUserList($groupId);
        if ($groupMembers) {
            collect($groupMembers)->each(function ($member) {
                if ($member->user_id && !Gateway::isUidOnline($member->user_id)) { // 群内不在线的用户做消息提醒
                    $this->groupMessageBadgeRepository->upBadge($member->user_id, $member->group_id);
                }
            });
        }
        return $this->success();
    }

    /**
     * websocket 初始化，用户加入群和登录通知
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function init(Request $request)
    {
        $connectId = $request->get('connect_id');
        if (empty($connectId) || empty($request->user()->id)) {
            return $this->badRequest();
        }
        $uid = $request->user()->id;
        if (Gateway::isUidOnline($uid)) {
            return $this->fail(__('the account has been logged in elsewhere'));
        }
        Gateway::bindUid($connectId, $uid);
        $groupList = $this->userRepository->groupList($uid);
        $this->connectJoinGroup($groupList, $connectId);
        $notifyMes = $this->message($request, [
            'type' => $this->getType('notify'),
            'data' => 'login',
        ]);
        Gateway::sendToUid($uid, $notifyMes);
        $this->notifyToAll($request, 'login');
        $this->saveClientIdToCache($uid, $connectId);
        $chatBadge = $this->chatMessageBadgeRepository->getBadgeByUid($uid);
        $groupBadge = $this->groupMessageBadgeRepository->getBadgeByUid($uid);
        $badgeResponse = [];
        collect($chatBadge)->each(function ($item) use (&$badgeResponse) {
            $badgeResponse[] = ['id' => $item->chat_id, 'is_group' => false, 'count' => $item->count];
        });
        collect($groupBadge)->each(function ($item) use (&$badgeResponse) {
            $badgeResponse[] = ['id' => $item->group_id, 'is_group' => true, 'count' => $item->count];
        });
        $applyNotify = $this->chatApplyRepository->getNotifyByUid($uid);
        $notifyCount = $this->userNotifyBadgeRepository->getBadgeByUid($uid);
        $friendsList = $this->userRepository->friendsListDetailed($uid);
        $groupList = $this->userRepository->groupList($uid);
        return $this->successWithData([
            'type'               => 'init',
            'badge_list'         => $badgeResponse, // 好友和群列表的通知徽标数
            'apply_notify'       => $applyNotify, // 系统通知列表
            'apply_notify_badge' => $notifyCount, // 系统通知列表徽标数
            'friend_list'        => $friendsList, // 好友列表
            'group_list'         => $groupList,   // 群列表
        ]);
    }

    /**
     * websocket断开
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function onConnectClose(Request $request)
    {
        if (empty($request->user()->id)) {
            return $this->badRequest();
        }
        $this->notifyToAll($request, 'close');
    }

    /**
     * 给用户所有好友和群发送通知消息
     *
     * @param Request $request
     * @param         $message
     */
    public function notifyToAll(Request $request, $message)
    {
        $groupIds = [];
        $notifyMes = $this->message($request, [
            'type' => $this->getType('notify'),
            'data' => $message,
        ]);
        $uid = $request->user()->id;
        $connectId = Gateway::getClientIdByUid($uid);
        $groupList = $this->userRepository->groupList($uid);
        if ($groupList) {
            collect($groupList)->each(function ($item) use (&$groupIds) {
                if ($item['group_id']) {
                    $groupIds[] = $item['group_id'];
                }
            });
            if (sizeof($groupIds)) {
                Gateway::sendToGroup($groupIds, $notifyMes, $connectId);
            }
        }
        $friendList = $this->userRepository->friendsListDetailed($uid);
        if (sizeof($friendList)) {
            $userIds = [];
            foreach ($friendList as $v) {
                $userIds[] = $v['id'];
            }
            Gateway::sendToUid($userIds, $notifyMes);
        }
    }

    public function saveWebsiteBadge(Request $request)
    {
        if (empty($request->get('badge'))) {
            return $this->badRequest();
        }
        $uid = $request->user()->id;
        $badgeList = $request->get('badge');
        if (sizeof($badgeList)) {
            foreach ($badgeList as $badge) {
                if ((int)$badge['count'] > 0 && ($badge['is_group'] == false || $badge['is_group'] == 'false')) {
                    $this->chatMessageBadgeRepository->setBadgeCount($uid, (int)$badge['id'], $badge['count']);
                }
                if ((int)$badge['count'] > 0 && ($badge['is_group'] == true || $badge['is_group'] == 'true')) {
                    $this->groupMessageBadgeRepository->setBadgeCount($uid, (int)$badge['id'], $badge['count']);
                }
            }
        }
        return $this->success();
    }
}
