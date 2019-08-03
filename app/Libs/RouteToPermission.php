<?php

namespace App\Libs;


use Spatie\Permission\Models\Permission;

class RouteToPermission
{
    protected $routeList;

    public function __construct()
    {
        $this->routeList = new RouteList();
    }

    public function permission(array $route, $guardName)
    {
        if (sizeof($route) == 0 && empty($route['name'])) {
            return false;
        }
        Permission::findOrCreate($route['name'], $guardName);
        return true;
    }

    public function create($guardName = 'chat')
    {
        $list = $this->routeList->getRoutes();
        if (sizeof($list)) {
            $event = $this;
            collect($list)->each(function ($item) use($event, $guardName) {
                $event->permission($item, $guardName);
            });
        }
    }
}