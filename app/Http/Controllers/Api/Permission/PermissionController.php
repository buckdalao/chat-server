<?php

namespace App\Http\Controllers\Api\Permission;

use App\Libs\RouteToPermission;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 将所有路由写入到权限列表，并赋予给root
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * 权限分页列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionList(Request $request)
    {
        $selection = $request->get('s');
        $selection = $selection == 'all' ? '' : $selection;
        if ($request->get('keyword')) {
            if ($selection == 'protected') {
                $list = Permission::query()->where('name', 'like', "%".$request->get('keyword'))
                    ->where('name', 'like', '%' . $selection)->paginate(15);
            } elseif ($selection == 'public') {
                $list = Permission::query()->where('name', 'like', "%".$request->get('keyword'))
                    ->where('name', 'not like', '%protected')->paginate(15);
            } else {
                $list = Permission::query()->where('name', 'like', "%" . $request->get('keyword'))->paginate(15);
            }
        } else {
            if ($selection == 'protected') {
                $list = Permission::query()->where('name', 'like', '%' . $selection)->paginate(15);
            } elseif ($selection == 'public') {
                $list = Permission::query()->where('name', 'not like', '%protected')->paginate(15);
            } else {
                $list = Permission::query()->paginate(15);
            }
        }
        collect($list->items())->map(function ($item) {
            $permission = Permission::findByName($item->name, $item->guard_name);
            $item['roles'] = $permission->getRoleNames();
            return $item;
        });
        return $this->successWithData($list);
    }

    /**
     * 权限赋予给指定角色
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoles(Request $request)
    {
        Validator::make($request->all(), [
            'roles' => 'required|array',
            'guard' => 'required|string',
            'permission' => 'required|int'
        ])->validate();
        $permissionID = $request->get('permission');
        $guard = $request->get('guard');
        $roles = $request->get('roles');
        $permission = Permission::findById($permissionID, $guard);
        $permission->syncRoles($roles);
        return $this->success();
    }

    /**
     * 删除权限
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePermission($permissionID, $guard)
    {
        Validator::make([
            'guard' => $guard,
            'permission' => $permissionID
        ], [
            'guard' => 'required|string',
            'permission' => 'required|int'
        ])->validate();
        $permission = Permission::findById($permissionID, $guard);
        // 权限上移除已存在的角色
        $allRole = $permission->getRoleNames();
        collect($allRole)->each(function($role) use ($permission) {
            $permission->removeRole($role);
        });
        // 移除角色已拥有该权限
        $hasPermissionUsers = User::permission($permission)->get();
        collect($hasPermissionUsers)->each(function ($item) use ($permission) {
            User::find($item->id)->revokePermissionTo($permission);
        });
        $permission->delete();
        return $this->success();
    }

}
