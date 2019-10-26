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
        parent::__construct();
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
            return $this->badRequest(__('parameter error'));
        }
        $res = $this->chatGroupMessageRepository->getGroupMessage($groupId, $limit ?: 50)->toArray();
        if ($res['current_page'] != $res['last_page'] && \request()->get('getLast')) {
            \request()->offsetSet('page', $res['last_page']);
            $res = $this->chatGroupMessageRepository->getGroupMessage($groupId, $limit ?: 50)->toArray();
        }
        return $this->successWithData($res);
    }
}
