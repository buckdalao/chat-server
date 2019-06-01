<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupMessageBadgeRepository;
use App\Repositories\Chat\ChatMessageBadgeRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatMessageBadgeController extends Controller
{
    protected $chatMessageBadgeRepository;

    protected $groupMessageBadgeRepository;

    public function __construct(ChatMessageBadgeRepository $chatMessageBadgeRepository,
                                ChatGroupMessageBadgeRepository $groupMessageBadgeRepository)
    {
        $this->chatMessageBadgeRepository = $chatMessageBadgeRepository;
        $this->groupMessageBadgeRepository = $groupMessageBadgeRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetBadge(Request $request)
    {
        if (empty($request->user()->id) || $this->requestIsEmpty($request, ['id'])) {
            return $this->badRequest();
        }
        $uid = $request->user()->id;
        $isGroup = $request->get('is_group');
        $id = $request->get('id');
        if ($isGroup) {
            $this->groupMessageBadgeRepository->resetBadge($uid, $id);
        } else {
            $this->chatMessageBadgeRepository->resetBadge($uid, $id);
        }
        return $this->success();
    }
}
