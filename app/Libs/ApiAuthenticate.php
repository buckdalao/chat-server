<?php

namespace App\Libs;


use Illuminate\Http\Request;

class ApiAuthenticate
{
    protected $secretId;
    protected $timestamp;
    protected $random;
    protected $key;
    protected $appId;

    public function param($secretId, $timestamp, $random, $key, $appId)
    {
        $this->secretId = $secretId;
        $this->timestamp = (int)$timestamp;
        $this->random = $random;
        $this->key = $key;
        $this->appId = $appId;
        return $this;
    }

    public function setRequest(Request $request)
    {
        $key = $request->header('client-key');
        $originStr = strstr(base64_decode($key), 'u');
        $arr = explode('&', $originStr);
        if (sizeof($arr)) {
            foreach ($arr as $v) {
                $d = explode('=', $v);
                if ($d[0] == 'u') {
                    $this->appId = $d[1];
                }
                if ($d[0] == 'k') {
                    $this->secretId = $d[1];
                }
                if ($d[0] == 'r') {
                    $this->random = $d[1];
                }
                if ($d[0] == 't') {
                    $this->timestamp = $d[1];
                }
            }
            $this->key = $key;
        }
        return $this;
    }

    protected function verifyParam()
    {
        if (empty($this->secretId) || empty($this->timestamp) || empty($this->random)) {
            return false;
        }
        if (time() - $this->timestamp > 180 || $this->timestamp - time() > 180) {
            return false;
        }
        if (!app('App\Repositories\Tool\ClientAuthenticateRepository')->authenticate($this->secretId)) {
            return false;
        }
        return true;
    }

    public function verify()
    {
        if (!$this->verifyParam()) {
            return false;
        }
        $info = app('App\Repositories\Tool\ClientAuthenticateRepository')->info($this->secretId);
        $original = 'u=' . $info->app_id . '&k=' . $this->secretId . '&t=' . $this->timestamp . '&r=' . $this->random . '&f=';
        $signStr = base64_encode(hash_hmac('sha1', $original, $info->secret_key) . $original);
        if ($this->key == $signStr) {
            return true;
        } else {
            return false;
        }
    }

    public function defaultEncrypt()
    {
        $u = env('API_APP_ID');
        $k = env('SECRET_ID');
        $t = time();
        $r = mt_rand(10000000, 99999999);
        $original = 'u=' . $u . '&k=' . $k . '&t=' . $t . '&r=' . $r . '&f=';
        $signStr = base64_encode(hash_hmac('sha1', $original, env('SECRET_KEY')) . $original);
        return $signStr;
    }
}