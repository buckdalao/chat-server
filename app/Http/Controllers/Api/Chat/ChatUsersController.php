<?php

namespace App\Http\Controllers\Api\Chat;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Repositories\Chat\ChatUsersRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatUsersController extends Controller
{
    use WsMessageTrait, BaseChatTrait;
    protected $chatUserRepository;

    public function __construct(ChatUsersRepository $chatUsersRepository)
    {
        parent::__construct();
        Gateway::$registerAddress = getenv('REGISTER_SERVER');
        $this->chatUserRepository = $chatUsersRepository;
    }

    /**
     * 是否已建立好友
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function isFriends(Request $request, $friendId)
    {
        if (empty($request->user()->id) || empty($friendId) || $request->user()->id == $friendId) {
            return $this->badRequest(__('parameter error'));
        }
        $res = $this->chatUserRepository->isFriends($request->user()->id, $friendId);
        return $this->successWithData($res);
    }

    /**
     * 添加好友
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function becomeFriends(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('friend_id'))){
            return $this->badRequest(__('parameter error'));
        }
        if ($this->chatUserRepository->isFriends($request->user()->id, $request->get('friend_id')) == false) {
            $res = $this->chatUserRepository->becomeFriends($request->user()->id, $request->get('friend_id'));
            return $this->successWithData($res);
        }else {
            return $this->badRequest();
        }
    }

    /**
     * 解除好友
     *
     * @param Request $request
     * @param         $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unFriend (Request $request, $chatId)
    {
        $uid = $request->user()->id;
        $fid = $this->chatUserRepository->getFriendIdByChatId($uid, $chatId);
        if ($fid == 0) {
            return $this->badRequest(__('parameter error'));
        }
        $this->chatUserRepository->unFriend($chatId);
        // 通知客户端更新好友列表
        if (Gateway::isUidOnline($fid)) {
            Gateway::sendToUid($fid, $this->message($request, [
                'type' => $this->getType('release_friend_list'),
                'data' => 0
            ]));
        }
        if (Gateway::isUidOnline($uid)) {
            Gateway::sendToUid($uid, $this->message($request, [
                'type' => $this->getType('release_friend_list'),
                'data' => 0
            ]));
        }
        return $this->success();
    }
}
