<?php

namespace App\Libs\Traits;


trait JsonResponseTrait
{

    public function success($mess = null)
    {
        if (is_array($mess)){
            $data = $mess;
            $data['message'] = $mess['message'] ? $mess['message'] : 'success';
            $data['time'] = time();
            $data['status_code'] = 200;
        } else {
            $data['message'] = $mess ? $mess : 'success';
            $data['time'] = time();
            $data['status_code'] = 200;
        }
        return response()->json($data);
    }

    public function fail($mess = null, $code = 500)
    {
        if (is_array($mess)){
            $data = $mess;
            $data['message'] = $mess['message'] ? $mess['message'] : 'failed';
            $data['time'] = time();
            $data['status_code'] = $code;
        } else {
            $data['message'] = $mess ? $mess : 'failed';
            $data['time'] = time();
            $data['status_code'] = $code;
        }
        return response()->json($data);
    }

}