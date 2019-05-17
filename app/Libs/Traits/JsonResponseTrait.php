<?php

namespace App\Libs\Traits;


use Symfony\Component\HttpFoundation\Response;

trait JsonResponseTrait
{

    public function success($mess = null, $code = Response::HTTP_OK)
    {
        if (is_array($mess)) {
            $data = $mess;
            $data['message'] = $mess['message'] ? $mess['message'] : 'success';
            $data['time'] = time();
            $data['status_code'] = $code;
        } else {
            $data['message'] = $mess ? $mess : 'success';
            $data['time'] = time();
            $data['status_code'] = $code;
        }
        return response()->json($data, $code);
    }

    public function fail($mess = null, $code = Response::HTTP_BAD_GATEWAY)
    {
        if (is_array($mess)) {
            $data = $mess;
            $data['message'] = $mess['message'] ? $mess['message'] : 'failed';
            $data['time'] = time();
            $data['status_code'] = $code;
        } else {
            $data['message'] = $mess ? $mess : 'failed';
            $data['time'] = time();
            $data['status_code'] = $code;
        }
        return response()->json($data, $code);
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
            'message'     => $mes ?: 'Bad Request',
            'time'        => time(),
            'status_code' => 400
        ], 400);
    }

}