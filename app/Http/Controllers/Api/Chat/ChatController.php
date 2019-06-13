<?php

namespace App\Http\Controllers\Api\Chat;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Repositories\Chat\ChatGroupMessageBadgeRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatMessageBadgeRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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


    public function __construct(ChatUsersRepository $chatUsersRepository, UserRepository $userRepository,
                                ChatMessageBadgeRepository $chatMessageBadgeRepository,
                                ChatGroupMessageBadgeRepository $groupMessageBadgeRepository,
                                ChatGroupUserRepository $chatGroupUserRepository)
    {
        Gateway::$registerAddress = env('REGISTER_SERVER');
        $this->chatUsersRepository = $chatUsersRepository;
        $this->userRepository = $userRepository;
        $this->chatMessageBadgeRepository = $chatMessageBadgeRepository;
        $this->groupMessageBadgeRepository = $groupMessageBadgeRepository;
        $this->chatGroupUserRepository = $chatGroupUserRepository;
    }

    /**
     * 两人对话消息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function onChatMessage(Request $request)
    {
        if ($this->requestIsEmpty($request, ['chat_id', 'content']) || empty($request->user()->id)) {
            return $this->badRequest();
        }
        $chatId = $request->get('chat_id');
        $content = $request->get('content');
        $uid = $request->user()->id;
        $fid = $this->chatUsersRepository->getFriendIdByChatId($uid, $chatId);
        if ($fid) {
            Gateway::sendToUid($fid, $this->message($request, [
                'type'    => $this->getType('message'),
                'data'    => $content,
                'chat_id' => $chatId
            ]));
            if (!Gateway::isUidOnline($fid)) { // 好友不在线做提醒
                $this->chatMessageBadgeRepository->setBadge($fid, $chatId);
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
        if ($this->requestIsEmpty($request, ['group_id', 'content']) || empty($request->user()->id)) {
            return $this->badRequest();
        }
        $groupId = $request->get('group_id');
        $content = $request->get('content');
        $uid = $request->user()->id;
        $connectId = Gateway::getClientIdByUid($uid);
        $groupUser = $this->chatGroupUserRepository->getGroupUserInfo($groupId, $uid);
        $userName = $groupUser->group_user_name ? $groupUser->group_user_name : $request->user()->name;
        Gateway::sendToGroup($groupId, $this->message($request, [
            'type'      => $this->getType('message'),
            'data'      => $content,
            'group_id'  => $groupId,
            'user_name' => $userName
        ]), $connectId);
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
                    $this->groupMessageBadgeRepository->setBadge($member->user_id, $member->group_id);
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
        if ($this->requestIsEmpty($request, ['connect_id']) || empty($request->user()->id)) {
            return $this->badRequest();
        }
        $uid = $request->user()->id;
        $connectId = $request->get('connect_id');
        if (Gateway::isUidOnline($uid)) {
            return $this->fail('该账号已在别处登录');
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
        return $this->successWithData([
            'type'       => 'init',
            'badge_list' => $badgeResponse
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
}
