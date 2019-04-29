<?php

namespace App\Exceptions;

use Exception;
use Dingo\Api\Exception\Handler as DingoHandler;

class ApiHandler extends DingoHandler
{
    public function handle(Exception $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['message' => 'Unauthorized', 'status_code' => 401, 'time' => time()], 401);
        }
        return parent::handle($exception);
    }
}