<?php

namespace App\Libs\Worker;

use App\Libs\Traits\GlobalDataTrait;
use GatewayWorker\Lib\Gateway;

class Handler
{
    use GlobalDataTrait;

    protected $connectId;

    protected $thisUid = 0;

    protected $response;

    protected $refreshToken;

    protected $debug = false;

    public function connect($connectId)
    {
        $this->connectId = $connectId;
        Gateway::joinGroup($connectId, 'all');
        ini_set('display_errors', 'off');
        error_reporting(E_ERROR);
    }

    public function onMessage($data)
    {
        $message = json_decode($data, true);
        $this->refreshToken = null;
        switch ($message['type']) {
            case 'login':
                // 消息类型不是登录视为非法请求，关闭连接
                if (empty($message['uid']) || empty($message['token']) || Gateway::isUidOnline($message['uid'])) {
                    return Gateway::closeClient($this->connectId);
                }
                $this->thisUid = (int)$message['uid'];
                Gateway::bindUid($this->connectId, $this->thisUid);
                if ($this->ping($message['token']) == false) { // 验证token
                    if ($this->debug) {
                        echo $message['token'] . "\r\n";
                        var_dump($this->response);
                    }
                    $message['content'] = 'Unauthorized';
                    Gateway::sendToCurrentClient($this->messagePack('error', $message));
                } else {
                    Gateway::sendToGroup('all', $this->messagePack('login'));
                }
                break;
            case 'message':
                if ($message['send_to_uid']) {
                    Gateway::sendToUid([$message['send_to_uid'], $message['uid']], $this->messagePack('msg', $message));
                } elseif ($message['group']) {
                    Gateway::sendToGroup($message['group'], $this->messagePack('msg', $message));
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

    protected function messagePack($type, $cont = [], $send_user = '')
    {
        $user = $this->registerServer()->getCurrentUser($this->thisUid);
        $mes = $cont['content'] ? $cont['content'] : '';
        $groups = Gateway::getAllGroupIdList();
        @sort($groups);
        foreach ($groups as $group) {
            $allGroup[] = ['group_name' => $group, 'img' => '/img/touxiang.png', 'group_id' => ''];
        }
        $data = [
            'type'           => $type,
            'content'        => $type == 'login' ? $user['name'] . '加入聊天室' : $mes,
            'user_name'      => $send_user ? $send_user : $user['user_name'],
            'time'           => date('Y-m-d H:i:s'),
            'from_uid'       => $this->thisUid,
            'from_client'    => $this->thisUid ? Gateway::getClientIdByUid($this->thisUid) : '',
            'all_user'       => $this->registerServer()->getGD($this->saveUserKey),
            'all_group'      => $allGroup,
            'send_to_group'  => $cont['group'] ? $cont['group'] : ($cont['send_to_uid'] ? '' : 'all'),
            'send_to_uid'    => $cont['send_to_uid'],
            'send_to_client' => $cont['send_to_uid'] ? Gateway::getClientIdByUid($cont['send_to_uid']) : '',
            'token_type'     => $cont['token_type'],
            'server'         => 'Gateway'
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
            'type' => 'pong'
        ]);
        Gateway::sendToCurrentClient($str);
    }
}