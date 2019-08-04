<?php

namespace App\Http\Controllers\Api\Chat\Util;

use App\Repositories\Tool\ClientAuthenticateRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function PHPSTORM_META\map;

class ClientKeyController extends Controller
{
    protected $clientAuthenticate;

    public function __construct(ClientAuthenticateRepository $clientAuthenticateRepository)
    {
        parent::__construct();
        $this->clientAuthenticate = $clientAuthenticateRepository;
    }

    /**
     * 授权码列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientAuthList(Request $request)
    {
        $res = $this->clientAuthenticate->keyList($request->get('keyword'));
        if ($res->items()) {
            collect($res->items())->map(function($item) {
                if ($item->status == 0 && $item->expire_time != 0 && $item->expire_time < time()) {
                    $item->status = 1;
                }
            });
        }
        return $this->successWithData($res);
    }

    public function setToken(Request $request)
    {

    }
}
