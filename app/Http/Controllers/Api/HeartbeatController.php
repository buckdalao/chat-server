<?php

namespace App\Http\Controllers\Api;

use GatewayClient\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HeartbeatController extends Controller
{
    //
    public function __construct()
    {
        Gateway::$registerAddress = env('REGISTER_SERVER');
    }

    public function getGlobal()
    {
        return response()->json($this->registerServer()->getGD(request()->get('key')));
    }

    public function ping(Request $request)
    {
        return $this->response->array(['pong' => \request()->get('ping'), 'token' => $request->headers->get('Authorization')]);
    }
}
