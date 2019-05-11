<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\ChatUsersRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatUsersController extends Controller
{
    protected $chatUserRepository;

    public function __construct(ChatUsersRepository $chatUsersRepository)
    {
        $this->chatUserRepository = $chatUsersRepository;
    }

    public function isFriends(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('friend_id'))) {
            return $this->fail('Parameter error');
        }
        $res = $this->chatUserRepository->isFriends($request->user()->id, $request->get('friend_id'));
        return $this->successWithData($res);
    }

    public function becomeFriends(Request $request)
    {
        if (empty($request->user()->id) || empty($request->get('friend_id'))){
            return $this->fail('Parameter error');
        }
        $res = $this->chatUserRepository->becomeFriends($request->user()->id, $request->get('friend_id'));
        return $this->successWithData($res);
    }
}
