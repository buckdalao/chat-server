<?php

namespace App\Http\Controllers;

use App\Libs\Traits\LibBaseTrait;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Helpers, LibBaseTrait;

    public function __construct()
    {
        if (request()->route()) {
            $currentRouteName = request()->route()->getName();
            $suffix = collect(explode('.', $currentRouteName))->last();
            if ($suffix == 'protected') {
                $this->middleware('permission:' . $currentRouteName);
            }
        }
    }
}
