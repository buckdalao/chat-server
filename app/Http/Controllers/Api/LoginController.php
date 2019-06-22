<?php

namespace App\Http\Controllers\Api;

use App\Libs\Traits\WsMessageTrait;
use App\Repositories\Chat\ChatApplyRepository;
use GatewayClient\Gateway;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use AuthenticatesUsers, WsMessageTrait;

    protected $chatApplyRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ChatApplyRepository $chatApplyRepository)
    {
//        $this->middleware('jwt.refresh');

        Gateway::$registerAddress = env('REGISTER_SERVER');

        $this->chatApplyRepository = $chatApplyRepository;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        /*$validator = $this->validator($request->all());
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->messages()->first());
        }*/

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        /*if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }*/

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
//        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            $this->username() => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => '邮箱格式错误',
            'password.required' => '密码格式错误',
            'email.string' => '邮箱格式错误',
            'password.string' => '密码格式错误',
            'email.email' => '邮箱格式错误',
            'password.min' => '密码太短',
        ]);
    }

    public function test(Request $request)
    {
        /* $exp = auth('api')->getPayload()->get('exp');
        $exp = date('Y-m-d H:i:s', $exp); */
        $res = app('App\Repositories\Chat\ChatApplyRepository')->getNotify(1);
        dd($res->toArray());
        echo date('Y-m-d H:i:s');
        return response()->json();
    }
}
