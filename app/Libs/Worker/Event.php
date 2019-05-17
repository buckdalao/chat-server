<?php

namespace App\Libs\Worker;

use App\Libs\Worker\Handler;

class Event
{
    protected static $handler;
    public static function onWorkerStart ($businessWorker)
    {
        self::$handler = new Handler();
    }

    public static function onConnect ($connectionId)
    {
        self::$handler->connect($connectionId);
    }

    public static function onMessage ($connectionId, $data)
    {
        self::$handler->onMessage($connectionId, $data);
    }

    public static function onClose ($connectionId)
    {

    }
}