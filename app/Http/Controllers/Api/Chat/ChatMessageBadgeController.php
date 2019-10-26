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
        parent::__construct();
        $this->chatMessageBadgeRepository = $chatMessageBadgeRepository;
        $this->groupMessageBadgeRepository = $groupMessageBadgeRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetBadge(Request $request)
    {
        $id = $request->get('id');
        if (empty($request->user()->id) || empty($id)) {
            return $this->badRequest();
        }
        $uid = $request->user()->id;
        $isGroup = $request->get('is_group');
        if ($isGroup && $isGroup != 'false') {
            $this->groupMessageBadgeRepository->resetBadge($uid, $id);
        } else {
            $this->chatMessageBadgeRepository->resetBadge($uid, $id);
        }
        return $this->success();
    }
}
