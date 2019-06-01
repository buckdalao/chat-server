<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2019/5/8
 * Time: 17:34
 */

namespace App\Libs\Traits;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

trait WsMessageTrait
{
    protected $chatId;

    protected $groupId;

    protected $mesData;

    protected $saveMax = 50;

    protected $mesType = [0 => 'message', 1 => 'notify', 2 => 'pong', 3 => 'connect', 5 => 'error', 6 => 'refresh_token'];

    public function setChatId($chatId)
    {
        $this->chatId = (int)$chatId;
        return $this;
    }

    public function setGroupId($gid)
    {
        $this->groupId = (int)$gid;
        return $this;
    }

    public function getType($t)
    {
        if (is_string($t)) {
            return array_search($t, $this->mesType) ?: 0;
        } else {
            return $this->mesType[$t] ?: 'message';
        }
    }

    public function setMessage(array $mes, $disable = false)
    {
        $data = [
            'type'      => $mes['type'],
            'data'      => $mes['data'],
            'uid'       => (int)$mes['uid'],
            'user_name' => $mes['user_name'],
            'chat_id'   => (int)$this->chatId ?: 0,
            'group_id'  => (int)$this->groupId ?: 0,
            'time'      => Carbon::now()->timestamp,
            'disable'   => $disable,
            'photo'     => $mes['photo']
        ];
        $this->mesData = $data;
        return $this;
    }

    public function saveRedis($exp = 3600 * 24)
    {
        if ($key = $this->getKey()) {
            if ($this->saveMax && Redis::llen($key) >= $this->saveMax) {
                Redis::lpop($key);
            }
            $expKey = $this->getKey(true);
            if ($expKey) {
                Redis::rpush($expKey, json_encode($this->mesData));
                if (Redis::ttl($expKey) < 0) {
                    Redis::expire($expKey, 3600 * 24 * 7);
                }
            }
            Redis::rpush($key, json_encode($this->mesData));
            if (Redis::ttl($key) < 0) {
                Redis::expire($key, $exp);
            }
        }
        $this->clear();
    }

    public function getKey($isQueue = false)
    {
        $exp = $isQueue ? ':queue' : '';
        if ($this->chatId) {
            return 'mes:toChat:' . $this->chatId . $exp;
        } elseif ($this->groupId) {
            return 'mes:toGroup:' . $this->groupId . $exp;
        } else {
            return '';
        }
    }

    public function getMessage($len = -1)
    {
        $key = $this->getKey();
        $data = [];
        if ($key) {
            $mes = Redis::lrange($key, 0, $len);
            foreach ($mes as $v) {
                $data[] = json_decode($v, true);
            }
        }
        return $data;
    }

    /**
     * @param        $callback   function namespace
     * @param string $key
     */
    public function saveQueueData($callback, $key = '')
    {
        $key = $key ? $key : $this->getKey(true);
        if ($key) {
            for ($i = 0; $i <= Redis::llen($key); $i++) {
                $data = Redis::lpop($key);
                if (!call_user_func_array($callback, [$data])) {
                    Redis::rpush($key, $data);
                }
            }
        }
    }

    public function saveAllQueueData($callbackToGroup, $callbackToUser)
    {
        $allKey = $this->getAllQueueKey();
        if (sizeof($allKey)) {
            foreach ($allKey as $key) {
                if (preg_match('/toGroup/', $key)) {
                    $this->saveQueueData($callbackToGroup, $key);
                } else {
                    $this->saveQueueData($callbackToUser, $key);
                }
            }
        }
    }

    public function getAllQueueKey()
    {
        return Redis::keys('mes:*:queue');
    }

    public function getKeySaveCount($key = null)
    {
        $key = $key ?: Redis::keys('mes:*');
        if (is_string($key)) {
            return Redis::llen($key);
        } elseif (is_array($key)) {
            $da = [];
            foreach ($key as $v) {
                $da[] = [
                    'key'   => $v,
                    'count' => Redis::llen($v)
                ];
            }
            return $da;
        }
    }

    public function clear()
    {
        $this->chatId = 0;
        $this->groupId = 0;
        $this->mesData = [];
    }

    public function ttl($key)
    {
        return Redis::ttl($key);
    }
}