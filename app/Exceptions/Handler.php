<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        /*if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['data' => __('auth.error.unauthorized'), 'status_code' => Response::HTTP_UNAUTHORIZED, 'time' => time()],
                Response::HTTP_UNAUTHORIZED);
        }*/
        if ($exception instanceof ValidationException) {
            return response()->json(['data' => $this->ValidationExceptionMessage($exception->errors()), 'status_code' => Response::HTTP_FORBIDDEN,
                'time' => time()], Response::HTTP_FORBIDDEN);
        }
        return parent::render($request, $exception);
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
