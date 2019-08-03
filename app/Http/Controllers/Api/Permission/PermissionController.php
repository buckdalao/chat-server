<?php

namespace App\Http\Controllers\Api\Permission;

use App\Libs\RouteToPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{

    public function routeToPermission()
    {
        $permission = new RouteToPermission();
        $permission->create();
        $list = Permission::all();
        $superAdminRoles = Role::findByName('root');
        if ($superAdminRoles) {
            $superAdminRoles->givePermissionTo($list);
        }
        return $this->success();
    }

}
