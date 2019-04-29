<?php

namespace App\Libs\Traits;


use GlobalData\Client;

trait GlobalDataTrait
{
    public $globalClient;

    public $saveUserKey = 'allUser';

    public $saveGroupKey = 'allGroups';

    /**
     *  注册global data服务
     *
     * @return $this
     */
    public function registerServer()
    {
        $this->globalClient = new Client(env('GLOBAL_SERVER') . ':' . env('GLOBAL_SERVER_PORT'));
        return $this;
    }

    /**
     *  添加登录用户到所有用户组
     *
     * @param $data
     */
    public function addUser($data)
    {
        $this->setList($this->saveUserKey, json_encode($data));
    }

    /**
     *  针对数组类型数据进行原子插入
     *
     * @param $key
     * @param $data
     * @return mixed
     */
    public function setList($key, $data)
    {
        do {
            $oldValue = $newValue = $this->globalClient->$key;
            $newValue[] = $data;
        } while (!$this->globalClient->cas($key, $oldValue, $newValue));
        return $this->globalClient->$key;
    }

    /**
     *  获取数据
     *
     * @param $key
     * @return mixed
     */
    public function getGD($key)
    {
        return $this->globalClient->$key;
    }

    /**
     *  删除用户列表中指定用户
     *
     * @param $id
     */
    public function deleteUser($id)
    {
        $key = $this->saveUserKey;
        $all = $this->globalClient->$key;
        $users = [];
        if (sizeof($all)) {
            foreach ($all as $v) {
                $u = json_decode($v, true);
                if ($u['id'] != $id) {
                    $users[] = $v;
                }
            }
            if (sizeof($users)) {
                $this->globalClient->$key = $users;
            } else {
                $this->deleteKey($key);
            }
        }
    }

    public function getCurrentUser($id)
    {
        $key = $this->saveUserKey;
        $all = $this->globalClient->$key;
        $current = [];
        if (sizeof($all)) {
            foreach ($all as $v) {
                $u = json_decode($v, true);
                if ($u['id'] == $id) {
                    $current = $u;
                    break;
                }
            }
        }
        return $current;
    }

    /**
     *  删除所有数据
     *
     * @param $key
     */
    public function deleteKey($key)
    {
        unset($this->globalClient->$key);
    }
}