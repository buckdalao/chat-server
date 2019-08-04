<?php

namespace App\Http\Controllers\Api\Permission;

use App\Libs\RouteToPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function routeToPermission()
    {
        $permission = new RouteToPermission();
        $permission->create();
        $list = Permission::all();
        $superAdminRoles = Role::findByName('root', 'chat');
        if ($superAdminRoles) {
            $superAdminRoles->givePermissionTo($list);
        }
        return $this->success();
    }

    public function permissionList(Request $request)
    {
        if ($request->get('keyword')) {
            $list = Permission::query()->where('name', 'like', "%".$request->get('keyword'))->paginate(15);
        } else {
            $list = Permission::query()->paginate(15);
        }
        return $this->successWithData($list);
    }

}
