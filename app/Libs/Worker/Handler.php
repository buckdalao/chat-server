<?php

namespace App\Libs\Worker;

use App\Libs\Traits\BaseChatTrait;
use App\Libs\Traits\WsMessageTrait;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class Handler
{
    use WsMessageTrait, BaseChatTrait;


    protected $thisUid = 0;

    protected $response;

    protected $refreshToken;

    protected $debug = false;

    public function connect($connectId)
    {
        Gateway::sendToCurrentClient($this->messagePack('connect', ['content' => $connectId]));
    }

    public function onMessage($connectionId, $data)
    {
        if (!$this->isJson($data)) {
            return;
        }
        $message = json_decode($data, true);
        $this->refreshToken = null;
        switch ($message['type']) {
            case 'ping':
                if (Arr::exists($message, 'token') && $this->ping($message['token']) == false) { // 验证token
                    if ($this->debug) {
                        echo $message['token'] . "\r\n";
                        var_dump($this->response);
                    }
                    $message['content'] = 'Unauthorized';
                    Gateway::sendToCurrentClient($this->messagePack('error', $message));
                }
                if ($this->refreshToken) {
                    $message['content'] = $this->refreshToken;
                    Gateway::sendToCurrentClient($this->messagePack('refresh_token', $message));
                } else {
                    $this->pong();
                }
                break;
        }
    }

    protected function messagePack($type, $cont = [])
    {
        $mes = $cont['content'] ? $cont['content'] : '';
        $data = [
            'type'       => $this->getType($type),
            'data'       => $mes,
            'time'       => Carbon::now()->timestamp,
            'uid'        => Arr::exists($cont, 'uid') ? (int)$cont['uid'] : 0,
            'user_name'  => Arr::exists($cont, 'user_name') ? $cont['user_name'] : '',
            'chat_id'    => Arr::exists($cont, 'chat_id') ? (int)$cont['chat_id'] : 0,
            'group_id'   => Arr::exists($cont, 'group_id') ? (int)$cont['group_id'] : 0,
            'token_type' => 'Bearer',
            'photo'      => Arr::exists($cont, 'photo') ? $cont['photo'] : '',
        ];
        return json_encode($data);
    }

    protected function ping($token)
    {
        $bool = true;
        $this->refreshToken = null;
        if (empty($token)) {
            return true;
        }
        try {
            $response = app('Dingo\Api\Dispatcher')->version('v1')->header('Authorization', $token)->post('lib/ping', ['ping' => 1]);
            $response = $response['data'];
            if (is_array($response)) {
                $explode = explode(' ', $token);
                $refreshToken = explode(' ', $response['token']);
                if ($explode[1] != $refreshToken[1]) {
                    $this->refreshToken = $refreshToken[1];
                }
            }
            $this->response = $response;
        } catch (\Exception $exception) {
            if ($exception instanceof \Dingo\Api\Exception\InternalHttpException) {
                $this->response = $exception->getResponse();
            }
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                $this->response = ['message' => 'Unauthorized', 'status_code' => 401, 'time' => time(), 'sign' => 'AuthenticationException'];
            }
            $bool = false;
        }
        return $bool;
    }

    protected function pong()
    {
        $str = json_encode([
            'type' => $this->getType('pong')
        ]);
        Gateway::sendToCurrentClient($str);
    }

    protected function isJson($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function onClose($connectionId)
    {
        $uid = $this->getUidByClientId($connectionId);
        $this->delCacheClientId($uid);
        if ($uid) {
            // 用户退出  通知相应的群和好友
            $response = app('App\Repositories\Chat\UserRepository')->groupList($uid);
            $groupIds = [];
            if (sizeof($response)) {
                foreach ($response as $group) {
                    if ($group['group_id']) {
                        $groupIds[] = $group['group_id'];
                    }
                }
                if (sizeof($groupIds)) {
                    Gateway::sendToGroup($groupIds, $this->messagePack('notify', ['content' => 'close', 'uid' => $uid]));
                }
            }
            $friendList = app('App\Repositories\Chat\UserRepository')->friendsListDetailed($uid);
            $friendIds = [];
            if (sizeof($friendList)) {
                foreach ($friendList as $friend) {
                    $friendIds[] = $friend['id'];
                }
                Gateway::sendToUid($friendIds, $this->messagePack('notify', ['content' => 'close', 'uid' => $uid]));
            }
        }
    }
}