<?php

namespace App\Repositories\Tool;

use App\Repositories\Chat\ChatGroupRepository;
use App\Repositories\Chat\ChatGroupUserRepository;
use App\Repositories\Chat\ChatUsersMessageRepository;
use App\Repositories\Chat\ChatUsersRepository;
use App\Repositories\Chat\UserRepository;
use App\Repositories\EloquentRepository;

class ChatCommonRepository  extends EloquentRepository
{
    protected $chatUsers;
    protected $chatUsersMessage;
    protected $chatGroup;
    protected $chatGroupUser;
    protected $user;

    public function __construct(ChatUsersRepository $chatUsersRepository,
                                ChatUsersMessageRepository $chatUsersMessageRepository,
                                ChatGroupRepository $chatGroupRepository,
                                ChatGroupUserRepository $chatGroupUserRepository,
                                UserRepository $userRepository)
    {
        $this->chatUsers = $chatUsersRepository;
        $this->chatUsersMessage = $chatUsersMessageRepository;
        $this->chatGroup = $chatGroupRepository;
        $this->chatGroupUser = $chatGroupUserRepository;
        $this->user = $userRepository;
    }

}