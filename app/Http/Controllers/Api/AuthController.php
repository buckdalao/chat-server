<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Chat\UserRepository;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
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
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
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
        return response()->json([
            'status_code'  => 200,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'users'        => auth('api')->user(),
            'friend_list'  => $friendsList,
            'group_list'   => $groupList,
        ]);
    }
}
