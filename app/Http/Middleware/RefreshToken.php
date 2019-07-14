<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class RefreshToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $newToken = null;
        $this->auth->unsetToken();
        $this->checkForToken($request);
        try {
            $user = $this->auth->parseToken()->setRequest($request)->authenticate();
            if (!$user) {
                return response()->json([
                    'status_code' => 401,
                    'data'        => __('auth.error.login_expired'),
                    'time'        => time(),
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            try {
                $newToken = $this->auth->refresh();

                /*
                 * 当使用ws服务时常驻进程变量释放问题(暂时是推测)，目前撸了一遍验证源码，发现是
                 * Tymon\JWTAuth\Manager 中refreshFlow值在第一次刷新之后就一直是true  导致后续
                 * Tymon\JWTAuth\Validators\PayloadValidator 的check方法中执行的是validateRefresh（刷新token）
                 */
                $this->auth->manager()->setRefreshFlow(false);

                $request->headers->set('Authorization', 'Bearer ' . $newToken); // 给当前的请求设置性的token,以备在本次请求中需要调用用户信息
            } catch (JWTException $e) {
                // 过期用户
                return response()->json([
                    'status_code' => 401,
                    'data'        => __('auth.error.login_expired'),
                    'error'       => $e->getMessage(),
                    'time'        => time(),
                ], 401);
            } catch (\Exception $exception) {
                return response()->json([
                    'status_code' => 500,
                    'data'        => __('unexpected mistakes occur'),
                    'error'       => $exception->getMessage(),
                    'time'        => time(),
                ], 500);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status_code' => 401,
                'data'        => __('auth.error.login_expired'),
                'error'       => $e->getMessage(),
                'time'        => time(),
            ], 401);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 500,
                'data'        => __('unexpected mistakes occur'),
                'error'       => $exception->getMessage(),
                'time'        => time(),
            ], 500);
        }
        $response = $next($request);

        if ($newToken) {
            $response->headers->set('Authorization', 'Bearer ' . $newToken);
        }
        return $response;
    }
}
