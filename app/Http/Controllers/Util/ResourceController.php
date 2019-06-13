<?php

namespace App\Http\Controllers\Util;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class ResourceController extends Controller
{
    public function getImage(Request $request)
    {
        if ($this->fileIsImage(base_path($request->path()))) {
            return response()->file(base_path($request->path()));
        } else {
            return $this->response()->error('Page Not Found', 404);
        }
    }

    public function getRecorder(Request $request)
    {
        $t = $request->get('t');
        if (empty($t)) {
            return $this->badRequest();
        }
        try {
            $u = app(\Tymon\JWTAuth\JWTAuth::class)->setToken($t)->authenticate();
        } catch (JWTException $exception) {
            return $this->fail($exception->getMessage());
        }
        $path = $this->getMediaPath($request->path());
        if ($this->isAudio($path)) {
            return response()->file($path);
        } else {
            return $this->response()->error('Page Not Found', 404);
        }
    }
}
