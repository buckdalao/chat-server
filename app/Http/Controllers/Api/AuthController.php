<?php

namespace App\Http\Controllers\Api;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use App\Repositories\Chat\UserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use WsMessageTrait, BaseChatTrait;
    protected $userRepository;

    /**
     * Create a new AuthController instance.
     * 要求附带email和password（数据来源users表）
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        // 这里额外注意了：官方文档样例中只除外了『login』
        // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        // 不过刷新一次作废
        $this->middleware('auth:api', ['except' => ['login']]);
        // 另外关于上面的中间件，官方文档写的是『auth:api』
        // 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回

        Gateway::$registerAddress = env('REGISTER_SERVER');
        $this->userRepository = $userRepository;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $res = DB::table('users')->select(['id'])->where(['email' => \request()->get('email')])->first();
        if (empty($res->id)) {
            return $this->fail('该邮箱未注册', 401);
        }
        if (Gateway::isUidOnline($res->id)) {
            return $this->fail('该账号已在别处登录', 401);
        }
        if (!$token = auth('api')->attempt($credentials)) {
            return $this->fail('请检查邮箱和密码是否正确', 401);
        }
        $friendsList = $this->userRepository->friendsListDetailed(auth('api')->user()->id);
        $groupList = $this->userRepository->groupList(auth('api')->user()->id);
        return $this->respondWithToken($token, $friendsList, $groupList);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth('api')->user()->toArray();
        $user['photo'] = asset($user['photo']);
        return $this->successWithData($user);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($request->get('badge')) {
            $uid = $request->user()->id;
            $badgeList = $request->get('badge');
            if (sizeof($badgeList)) {
                foreach ($badgeList as $badge) {
                    if ((int)$badge['count'] > 0 && ($badge['is_group'] == false || $badge['is_group'] == 'false')) {
                        app('App\Repositories\Chat\ChatMessageBadgeRepository')->setBadgeCount($uid, (int)$badge['id'], $badge['count']);
                    }
                    if ((int)$badge['count'] > 0 && ($badge['is_group'] == true || $badge['is_group'] == 'true')) {
                        app('App\Repositories\Chat\ChatGroupMessageBadgeRepository')->setBadgeCount($uid, (int)$badge['id'], $badge['count']);
                    }
                }
            }
        }
        auth('api')->logout();

        return $this->success('Successfully logged out');
    }

    /**
     * Refresh a token.
     * 刷新token，如果开启黑名单，以前的token便会失效。
     * 值得注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $friendsList = [], $groupList = [])
    {
        $users = auth('api')->user()->toArray();
        $users['photo'] = asset($users['photo']);
        return $this->successWithData([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'users'        => $users,
            'friend_list'  => $friendsList,
            'group_list'   => $groupList,
        ]);
    }

    /**
     * 修改个人信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInformation(Request $request)
    {
        Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:12'],
            'email'    => ['required', 'string', 'email'],
            'phone' => ['required', 'string', 'min:8'],
        ])->validate();
        $uid = $request->user()->id;
        if ($request->get('email') != $request->user()->email) {
            $emailUser = $this->userRepository->getUserByEmail($request->get('email'));
            if ($emailUser) {
                return $this->badRequest();
            }
        }
        if ($request->get('phone') != $request->user()->phone) {
            $phoneUser = $this->userRepository->getUserByPhone($request->get('phone'));
            if ($phoneUser) {
                return $this->badRequest();
            }
        }
        $this->userRepository->update($uid, $request->all());
        $friendsList = $this->userRepository->friendsListDetailed(auth('api')->user()->id);
        if ($friendsList) {
            foreach ($friendsList as $value) {
                // 通知在线好友更新好友列表
                if ($value['id'] && Gateway::isUidOnline($value['id'])) {
                    Gateway::sendToUid($value['id'], $this->message($request, [
                        'type' => $this->getType('release_friend_list'),
                        'data' => 0
                    ]));
                }
            }
        }
        return $this->success();
    }

    public function changePassword(Request $request)
    {
        Validator::make($request->all(), [
            'old_password' => ['required', 'string', 'min:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:6'],
        ])->validate();
        $password = Hash::make($request->get('password'));
        $uid = $request->user()->id;
        $userInfo = $this->userRepository->getUserById($uid);
        if (!Hash::check($request->get('old_password'), $userInfo->password)) {
            return $this->badRequest('Primitive password error');
        }
        if (Hash::check($request->get('password'), $userInfo->password)) {
            return $this->badRequest('The new password cannot be the same as the old one');
        }
        $this->userRepository->update($uid, [
            'password' => $password
        ]);
        return $this->success();
    }
}
