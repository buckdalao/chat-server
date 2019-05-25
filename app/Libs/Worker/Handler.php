<?php

namespace App\Libs\Worker;

use App\Libs\Traits\WsMessageTrait;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Carbon;

class Handler
{
    use WsMessageTrait;


    protected $thisUid = 0;

    protected $response;

    protected $refreshToken;

    protected $debug = false;

    public function connect($connectId)
    {
        ini_set('display_errors', 'off');
        error_reporting(E_ERROR);
    }

    public function onMessage($connectionId, $data)
    {
        if (!$this->isJson($data)) {
            return;
        }
        $message = json_decode($data, true);
        $this->refreshToken = null;
        switch ($message['type']) {
            case 'login':
                // 消息类型不是登录视为非法请求，关闭连接
                if (empty($message['uid']) || empty($message['token']) || Gateway::isUidOnline($message['uid'])) {
                    return Gateway::closeClient($connectionId);
                }
                $_SESSION['uid'] = (int)$message['uid'];
                Gateway::bindUid($connectionId, $message['uid']);
                if ($this->ping($message['token']) == false) { // 验证token
                    if ($this->debug) {
                        echo $message['token'] . "\r\n";
                        var_dump($this->response);
                    }
                    $message['content'] = 'Unauthorized';
                    Gateway::sendToCurrentClient($this->messagePack('error', $message));
                } else {
                    // 获取用户的群组  并加入群
                    $response = app('App\Repositories\Chat\UserRepository')->groupList($message['uid']);
                    $groupIds = [];
                    if (sizeof($response)) {
                        foreach ($response as $group) {
                            if ($group['group_id']) {
                                Gateway::joinGroup($connectionId, $group['group_id']);
                                $groupIds[] = $group['group_id'];
                            }
                        }
                        if (sizeof($groupIds)){
                            Gateway::sendToGroup($groupIds, $this->messagePack('notify', ['content' => 'login', 'uid' => $message['uid']]), $connectionId);
                        }
                    }
                    $friendList = app('App\Repositories\Chat\UserRepository')->friendsListDetailed($message['uid']);
                    $friendIds = [];
                    if (sizeof($friendList)) {
                        foreach ($friendList as $friend) {
                            $friendIds[] = $friend['id'];
                        }
                        Gateway::sendToUid($friendIds, $this->messagePack('notify', ['content' => 'login', 'uid' => $message['uid']]));
                    }
                    Gateway::sendToCurrentClient($this->messagePack('notify', ['content' => 'success']));
                }
                break;
            case 'message':
                $message['uid'] = Gateway::getUidByClientId($connectionId);
                if ($message['send_to_uid']) {
                    Gateway::sendToUid($message['send_to_uid'], $this->messagePack('message', $message));
                    $this->setChatId($message['chat_id'])->message($message, 'message')->saveRedis();
                    $this->clear();
                } elseif ($message['group_id']) {
                    Gateway::sendToGroup($message['group_id'], $this->messagePack('message', $message));
                    $this->setGroupId($message['group_id'])->message($message, 'message')->saveRedis();
                    $this->clear();
                }
                break;

            case 'ping':
                if ($this->ping($message['token']) == false) { // 验证token
                    if ($this->debug) {
                        echo $message['token'] . "\r\n";
                        var_dump($this->response);
                    }
                    $message['content'] = 'Unauthorized';
                    Gateway::sendToCurrentClient($this->messagePack('error', $message));
                }
                if ($this->refreshToken) {
                    $message['content'] = $this->refreshToken;
                    $message['token_type'] = 'Bearer';
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
            'uid'        => (int)$cont['uid'],
            'user_name'  => $cont['user_name'],
            'chat_id'    => (int)$cont['chat_id'] ?: 0,
            'group_id'   => (int)$cont['group_id'] ?: 0,
            'token_type' => $cont['token_type'],
            'photo'      => $cont['photo'],
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
        if ($_SESSION['uid']) {
            $uid = $_SESSION['uid'];
            // 用户退出  通知相应的群和好友
            $response = app('App\Repositories\Chat\UserRepository')->groupList($uid);
            $groupIds = [];
            if (sizeof($response)) {
                foreach ($response['data'] as $group) {
                    if ($group['group_id']) {
                        $groupIds[] = $group['group_id'];
                    }
                }
                if (sizeof($groupIds)) {
                    Gateway::sendToGroup($groupIds, $this->messagePack('notify', ['content' => 'logout', 'uid' => $uid]));
                }
            }
            $friendList = app('App\Repositories\Chat\UserRepository')->friendsListDetailed($uid);
            $friendIds = [];
            if (sizeof($friendList)) {
                foreach ($friendList as $friend) {
                    $friendIds[] = $friend['id'];
                }
                Gateway::sendToUid($friendIds, $this->messagePack('notify', ['content' => 'logout', 'uid' => $uid]));
            }
        }
    }
}