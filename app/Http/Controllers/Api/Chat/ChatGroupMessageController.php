<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupMessageRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatGroupMessageController extends Controller
{
    protected $chatGroupMessageRepository;

    public function __construct(ChatGroupMessageRepository $chatGroupMessageRepository)
    {
        $this->chatGroupMessageRepository = $chatGroupMessageRepository;
    }

    /**
     * 获取群消息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupMessage($groupId, $limit = 50)
    {
        if (empty($groupId) || !is_numeric($limit)) {
            return $this->badRequest('Parameter error');
        }
        $res = $this->chatGroupMessageRepository->getGroupMessage($groupId, $limit ?: 50);
        return $this->successWithData($res);
    }
}
