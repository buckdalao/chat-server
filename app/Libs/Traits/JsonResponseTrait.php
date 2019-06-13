<?php

namespace App\Libs\Traits;


use Symfony\Component\HttpFoundation\Response;

trait JsonResponseTrait
{

    public function success($mess = null, $code = Response::HTTP_OK)
    {
        $data['data'] = $mess ? $mess : 'success';
        $data['time'] = time();
        $data['status_code'] = $code;
        return response()->json($data, $code);
    }

    public function fail($mess = null, $code = Response::HTTP_FORBIDDEN)
    {
        $data['data'] = $mess ? $mess : 'failed';
        $data['time'] = time();
        $data['status_code'] = $code;
        return response()->json($data, $code);
    }

    public function failWithData($data, $code = Response::HTTP_FORBIDDEN)
    {
        return response()->json([
            'data'        => $data,
            'time'        => time(),
            'status_code' => $code
        ], $code);
    }

    public function successWithData($data, $code = Response::HTTP_OK)
    {
        return response()->json([
            'data'        => $data,
            'time'        => time(),
            'status_code' => $code
        ], $code);
    }

    public function badRequest($mes = '')
    {
        return response()->json([
            'data'     => $mes ?: 'Bad Request',
            'time'        => time(),
            'status_code' => 400
        ], 400);
    }

}