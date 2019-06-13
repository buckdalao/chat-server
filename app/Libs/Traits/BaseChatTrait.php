<?php

namespace App\Libs\Traits;


use GatewayClient\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

trait BaseChatTrait
{
    public function connectJoinGroup(array $groupList, $connectId)
    {
        if ($groupList) {
            collect($groupList)->each(function ($item) use ($connectId) {
                if ($item['group_id']) {
                    Gateway::joinGroup($connectId, $item['group_id']);
                }
            });
        }
    }

    public function message(Request $request, array $cont)
    {
        $mes = $cont['data'] ? $cont['data'] : '';
        $data = [
            'type'       => $cont['type'] ?: 0,
            'data'       => $mes,
            'time'       => Carbon::now()->timestamp,
            'uid'        => $request->user()->id,
            'user_name'  => Arr::exists($cont, 'user_name') ? $cont['user_name'] : $request->user()->name,
            'chat_id'    => Arr::exists($cont, 'chat_id') ? (int)$cont['chat_id'] : 0,
            'group_id'   => Arr::exists($cont, 'group_id') ? (int)$cont['group_id'] : 0,
            'token_type' => 'Bearer',
            'photo'      => asset($request->user()->photo),
        ];
        return json_encode($data);
    }

    public function saveClientIdToCache($uid, $clientId)
    {
        if ($uid && $clientId) {
            Redis::set('client:bind:' . $uid, $clientId);
            Redis::set('uid:bind:' . $clientId, $uid);
        }
    }

    public function delCacheClientId($uid)
    {
        if ($uid) {
            $cid = $this->getClientIdByUid($uid);
            Redis::del('client:bind:' . $uid);
            if ($cid) {
                Redis::del('uid:bind:' . $cid);
            }
        }
    }

    public function getUidByClientId($connectionId)
    {
        if ($connectionId) {
            return Redis::get('uid:bind:' . $connectionId);
        }
    }

    public function getClientIdByUid($uid)
    {
        if ($uid) {
            return Redis::get('client:bind:' . $uid);
        }
    }
}