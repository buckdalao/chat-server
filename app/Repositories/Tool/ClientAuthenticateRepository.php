<?php

namespace App\Repositories\Tool;

use App\Models\Tool\ClientAuthenticate;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Str;

class ClientAuthenticateRepository extends EloquentRepository
{

    public function __construct(ClientAuthenticate $model)
    {
        $this->model = $model;
    }

    /**
     *  client key 验证
     *
     * @param $token
     * @return bool
     */
    public function authenticate($token)
    {
        if ($token) {
            $res = $this->model->newQuery()->where('token', '=', $token)->first();
            if (!$res || ($res->expire_time && $res->expire_time < time()) || $res->status == 1) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $expireTime
     * @return string
     */
    public function setToken($expireTime)
    {
        $token = Str::uuid()->getHex();
        $this->create([
            'token'       => $token,
            'expire_time' => $expireTime ? time() + (int)$expireTime : $expireTime,
            'status'      => 0
        ]);
        return $token;
    }

    /**
     * @param $token
     * @return int
     */
    public function delToken($token)
    {
        return $this->model->newQuery()->where('token', '=', $token)->update(['status' => 1]);
    }

    /**
     * 获取client key的剩余时间 seconds  : -1 未设置 -2 未知key 0 已过期
     *
     * @param $token
     * @return mixed|null
     */
    public function expToken($token)
    {
        $res = $this->model->newQuery()->where('token', '=', $token)->where('status', '=', 0)->first(['expire_time']);
        if ($res) {
            if ($res->expire_time == 0) {
                return -1;
            }
            $timeRemain = time() > $res->expire_time ? 0 : $res->expire_time - time();
            return $timeRemain;
        } else {
            return -2;
        }
    }

    /**
     * 获取所有授权码
     *
     * @param null $keyword
     * @param int  $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function keyList($keyword = null, $limit = 15)
    {
        if ($keyword) {
            return $this->model->newQuery()->where('token', '=', $keyword)->paginate($limit ?? 15);
        }
        return $this->model->newQuery()->paginate($limit ?? 15);
    }
}