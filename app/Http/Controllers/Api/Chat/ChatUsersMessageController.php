<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatUsersMessageRepository;
use App\Repositories\Chat\ChatUsersRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatUsersMessageController extends Controller
{
    protected $chatUsersMessRepository;

    protected $chatUsersRepository;

    public function __construct(ChatUsersMessageRepository $chatUsersMessageRepository,
                                    ChatUsersRepository $chatUsersRepository)
    {
        $this->chatUsersMessRepository = $chatUsersMessageRepository;
        $this->chatUsersRepository = $chatUsersRepository;
    }

    /**
     * 获取登录用户对应好友的消息 参数 chat_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatMessageByChatId(Request $request, $chatId, $limit)
    {
        if (empty($request->user()->id) || empty($chatId)){
            return $this->badRequest();
        }
        $mesList= [];
        if($chatId){
            $mesList = $this->chatUsersMessRepository->chatMessage((int)$chatId, $limit ?: 50);
        }
        return $this->successWithData($mesList);
    }

    /**
     * 获取登录用户对应好友的消息 参数 friend_id
     *
     * @param Request $request
     * @param         $uid
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatMessageByUid(Request $request, $uid, $limit)
    {
        if (empty($request->user()->id) || empty($uid)){
            return $this->badRequest();
        }
        $chatId = 0;
        $chat = $this->chatUsersRepository->getChat($request->user()->id, $uid);
        if ($chat) {
            $chatId = $chat->id;
        }
        $mesList= [];
        if($chatId){
            $mesList = $this->chatUsersMessRepository->chatMessage((int)$chatId, $limit ?: 50);
        }
        return $this->successWithData($mesList);
    }
}
