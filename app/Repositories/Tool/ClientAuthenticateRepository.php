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
            'expire_time' => $expireTime,
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
     * @param $token
     * @return mixed|null
     */
    public function expToken($token)
    {
        $res = $this->model->newQuery()->where('token', '=', $token)->first(['expire_time']);
        if ($res) {
            return $res->expire_time;
        } else {
            return null;
        }
    }
}