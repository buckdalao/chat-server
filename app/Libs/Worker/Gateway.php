<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2019/5/29
 * Time: 9:46
 */

namespace App\Libs\Worker;

use GatewayClient\Gateway as BaseGateway;

class Gateway extends BaseGateway
{
    public function __construct()
    {
        self::$registerAddress = env('REGISTER_SERVER');
    }
}