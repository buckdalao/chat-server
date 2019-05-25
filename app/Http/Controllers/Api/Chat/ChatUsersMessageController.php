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
     * 获取登录用户对应好友的消息 参数 friend_id or chat_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserChatMessage(Request $request)
    {
        if (empty($request->user()->id) || (empty($request->get('friend_id')) && empty($request->get('chat_id')))){
            return $this->badRequest();
        }
        if ($request->get('chat_id')){
            $chatId = $request->get('chat_id');
        }else{
            $chat = $this->chatUsersRepository->getChat($request->user()->id, $request->get('friend_id'));
            $chatId = $chat->id;
        }
        $mesList= [];
        if($chatId){
            $mesList = $this->chatUsersMessRepository->chatMessage((int)$chatId);
        }
        return $this->successWithData($mesList);
    }
}
