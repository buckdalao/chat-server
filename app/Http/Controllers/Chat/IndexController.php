<?php

namespace App\Http\Controllers\Chat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class IndexController extends Controller
{

    public function index()
    {
        return response()->view('chat.home', [
            'title' => 'Home',
            'user' => \request()->user(),
            'isRoot' => \request()->user()->hasRole('root')
        ]);
    }

    public function getApiToken(Request $request)
    {
        $token = auth('api')->fromUser($request->user());
        if ($token) {
            return $this->successWithData([
                'token' => $token,
                'client_key' => env('SECRET_ID')
            ]);
        } else {
            return $this->fail(__('unexpected mistakes occur'), 401);
        }
    }

    public function deleteToken()
    {
        auth('api')->parseToken()->invalidate();
        return $this->success();
    }
}
