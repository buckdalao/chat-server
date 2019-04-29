<?php

namespace App\Http\Controllers\Util;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResourceController extends Controller
{
    public function getImage(Request $request)
    {
        if ($this->fileIsImage(base_path($request->path()))) {
            return response()->file(base_path($request->path()));
        }else{
            return $this->response()->error('Page Not Found', 404);
        }
    }
}
