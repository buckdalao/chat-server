<?php

namespace App\Repositories\Chat;

use App\Models\Chat\ChatUsersMessage;
use App\Repositories\EloquentRepository;

class ChatUsersMessageRepository  extends EloquentRepository
{

    public function __construct(ChatUsersMessage $model)
    {
        $this->model = $model;
    }
}