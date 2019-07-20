<?php

namespace App\Exceptions;

use Exception;
use Dingo\Api\Exception\Handler as DingoHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ApiHandler extends DingoHandler
{
    public function handle(Exception $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['data' => __('auth.error.unauthorized'), 'status_code' => Response::HTTP_UNAUTHORIZED, 'time' => time()],
                Response::HTTP_UNAUTHORIZED);
        }
        if ($exception instanceof ValidationException) {
            return response()->json(['data' => $this->ValidationExceptionMessage($exception->errors()), 'status_code' => Response::HTTP_FORBIDDEN,
                                     'time' => time()], Response::HTTP_FORBIDDEN);
        }
        return parent::handle($exception);
    }

    public function ValidationExceptionMessage(array $data)
    {
        foreach ($data as $v) {
            $mes = $v[0];
            break;
        }
        return $mes ? $mes : __('the given data was invalid');
    }
}