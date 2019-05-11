<?php

namespace App\Http\Controllers\Api\Chat;

use App\Repositories\Chat\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    public function getFriendsList(Request $request)
    {
        if (empty($request->user()->id)){
            return $this->fail('Parameter error');
        }
        $res = $this->userRepository->friendsListDetailed($request->user()->id);
        return $this->successWithData($res);
    }
}
