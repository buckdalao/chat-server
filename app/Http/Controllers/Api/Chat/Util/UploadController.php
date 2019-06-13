<?php

namespace App\Http\Controllers\Api\Chat\Util;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Libs\Upload\UploadFactory;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Chat\ChatGroupMessageBadgeRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatMessageBadgeRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserRepository;

class UploadController extends Controller
{
    use WsMessageTrait, BaseChatTrait;

    protected $chatUsersRepository;
    protected $userRepository;
    protected $chatMessageBadgeRepository;
    protected $groupMessageBadgeRepository;
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

    public function uploadRecorderByChat(Request $request, $chatId)
    {
        Validator::make($request->all(), [
            'media' => 'required'
        ])->validate();
        $res = UploadFactory::putFile($request->file('media'))->save();
        if (empty($res)) {
            return $this->fail('audio save failed');
        }
        $uid = $request->user()->id;
        $fid = $this->chatUsersRepository->getFriendIdByChatId($uid, $chatId);
        if ($fid) {
            Gateway::sendToUid([$fid, $uid], $this->message($request, [
                'type'    => $this->getType('audio'),
                'data'    => UploadFactory::mediaUrl($res->savePath, 'audio'),
                'chat_id' => $chatId
            ]));
            if (!Gateway::isUidOnline($fid)) { // 好友不在线做提醒
                $this->chatMessageBadgeRepository->setBadge($fid, $chatId);
            }
            // 消息缓存
            $this->setChatId($chatId)->setMessage([
                'type'      => $this->getType('audio'),
                'data'      => $res->savePath,
                'uid'       => $uid,
                'user_name' => $request->user()->name,
                'photo'     => asset($request->user()->photo),
            ])->saveRedis();
        }
        return $this->success();
    }

    public function uploadRecorderByGroup(Request $request, $groupId)
    {
        Validator::make($request->all(), [
            'media' => 'required'
        ])->validate();
        $res = UploadFactory::putFile($request->file('media'))->save();
        if (empty($res)) {
            return $this->fail('audio save failed');
        }
        $uid = $request->user()->id;
        $groupUser = $this->chatGroupUserRepository->getGroupUserInfo($groupId, $uid);
        $userName = $groupUser->group_user_name ? $groupUser->group_user_name : $request->user()->name;
        Gateway::sendToGroup($groupId, $this->message($request, [
            'type'      => $this->getType('audio'),
            'data'      => UploadFactory::mediaUrl($res->savePath, 'audio'),
            'group_id'  => $groupId,
            'user_name' => $userName
        ]));
        // 消息缓存
        $this->setGroupId($groupId)->setMessage([
            'type'      => $this->getType('audio'),
            'data'      => $res->savePath,
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
}
