<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2019/5/8
 * Time: 17:34
 */

namespace App\Libs\Traits;


use Illuminate\Support\Facades\Redis;

trait WsMessageTrait
{
    protected $uid;

    protected $toUid;

    protected $groupId;

    protected $mesData;

    protected $saveMax = 5;

    protected $mesType = [0 => 'message', 1 => 'notify'];

    public function setUid($uid, $toUid = 0)
    {
        $this->uid = (int)$uid;
        if ($toUid) {
            $this->toUid = (int)$toUid;
        }
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

    public function message($mes, $type, $disable = true)
    {
        $data = [
            'type'    => $this->getType($type),
            'data'    => $mes,
            'uid'     => $this->uid,
            'toUid'   => $this->toUid,
            'groupId' => $this->groupId,
            'date'    => date('Y-m-d H:i:s'),
            'time'    => time(),
            'disable' => $disable
        ];
        $this->mesData = $data;
        return $this;
    }

    public function saveRedis($exp = 3600 * 24)
    {
        if ($key = $this->getKey()) {
            if ($this->saveMax && Redis::llen($key) >= $this->saveMax) {
                $overflow = Redis::lpop($key);
                $expKey = $this->getKey(true);
                if ($overflow && $expKey) {
                    Redis::rpush($expKey, $overflow);
                    if (Redis::ttl($expKey) < 0) {
                        Redis::expire($expKey, 3600 * 24 * 7);
                    }
                }
            }
            Redis::rpush($key, json_encode($this->mesData));
            if (Redis::ttl($key) < 0) {
                Redis::expire($key, $exp);
            }
        }
    }

    public function getKey($isExpire = false)
    {
        $exp = $isExpire ? ':exp' : '';
        if ($this->uid && $this->toUid) {
            if ($this->uid < $this->toUid) {
                return 'mes:' . $this->uid . ':and:' . $this->toUid . $exp;
            } else {
                return 'mes:' . $this->toUid . ':and:' . $this->uid . $exp;
            }
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
            $data = Redis::lrange($key, 0, $len);
        }
        return $data;
    }

    /**
     * @param        $callback   function namespace
     * @param string $key
     */
    public function saveExpireData($callback, $key = '')
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

    public function saveAllExpireData($callback)
    {
        $allKey = $this->getAllExpireKey();
        if (sizeof($allKey)) {
            foreach ($allKey as $key) {
                $this->saveExpireData($callback, $key);
            }
        }
    }

    public function getAllExpireKey()
    {
        return Redis::keys('mes:*:exp');
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
        $this->uid = 0;
        $this->toUid = 0;
        $this->groupId = 0;
        $this->mesData = [];
    }

    public function ttl($key)
    {
        return Redis::ttl($key);
    }
}