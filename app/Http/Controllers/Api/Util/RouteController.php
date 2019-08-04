<?php

namespace App\Http\Controllers\Api\Util;

use App\Libs\ArrayPaginator;
use App\Libs\RouteList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class RouteController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function routeList(Request $request)
    {
        $route = new RouteList();
        $list = $route->getRoutes();
        if ($request->get('keyword')) {
            $list = collect($list)->filter(function ($item) use ($request) {
                if (Str::contains($item['name'], $request->get('keyword'))) {
                    return $item;
                }
            })->toArray();
        }
        if (sizeof($list) && $request->get('s')) {
            $list = collect($list)->filter(function ($item) use ($request) {
                if ($item['method'] == $request->get('s')) {
                    return $item;
                }
            })->toArray();
        }
        $pageClass = new ArrayPaginator();
        $data = $pageClass->setArray($list)->paginate($request->get('limit'));
        return $this->successWithData($data);
    }
}
