<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatGroupMessageRepository;
use App\Repositories\Chat\ChatUsersMessageRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatToolController extends Controller
{
    protected $chatUsersMessRepository;

    protected $chatGroupMessRepository;

    public function __construct(ChatUsersMessageRepository $chatUsersMessageRepository,
                                ChatGroupMessageRepository $chatGroupMessageRepository)
    {
        $this->chatUsersMessRepository = $chatUsersMessageRepository;
        $this->chatGroupMessRepository = $chatGroupMessageRepository;
    }
}
