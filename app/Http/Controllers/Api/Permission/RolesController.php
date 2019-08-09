<?php

namespace App\Http\Controllers\Api\Permission;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建root角色并赋予给操作用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSuperAdmin(Request $request)
    {
        if ($request->user()->id != 1) {
            return $this->fail('You have no right');
        }
        $role = Role::findOrCreate('root', 'chat');
        $request->user()->assignRole($role);
        return $this->success();
    }

    /**
     * 创建角色
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRoles(Request $request)
    {
        Validator::make($request->all(), [
            'roles' => 'required|string',
            'guard' => 'required|string',
        ])->validate();
        $roles = $request->get('roles');
        $guardName = $request->get('guard');
        Role::findOrCreate($roles, $guardName);
        return $this->success();
    }

    /**
     * 角色分页列表 支出keyword搜索
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleList(Request $request)
    {
        if ($request->get('keyword')) {
            $list = Role::query()->where('name', 'like', "%".$request->get('keyword'))->paginate(15);
        } else {
            $list = Role::query()->paginate(15);
        }
        return $this->successWithData($list);
    }

    /**
     * 获取所有角色
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allRoles(Request $request)
    {
        $list = [];
        if ($request->get('permission')) {
            Validator::make($request->all(), [
                'permission' => 'required|int',
                'guard' => 'required|string',
            ])->validate();
            $list = Role::all();
            $permission = Permission::findById($request->get('permission'), $request->get('guard') ?: 'chat');
            if (sizeof($list)) {
                $list = collect($list)->map(function($item) use($permission) {
                    $role = Role::findByName($item->name, $item->guard_name);
                    if ($role->hasPermissionTo($permission)) {
                        $item['have'] = true;
                    } else {
                        $item['have'] = false;
                    }
                    return $item;
                });
            }
        } else {
            $list = Role::all();
        }
        return $this->successWithData($list);
    }

    /**
     * 赋予角色给指定用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function giveRoleToUser(Request $request)
    {
        Validator::make($request->all(), [
            'roles' => 'required|array',
            'guard' => 'required|string',
            'uid' => 'required|int'
        ])->validate();
        $uid = $request->get('uid');
        $roles = $request->get('roles');
        $user = User::find($uid);
        $user->syncRoles($roles);
        return $this->success();
    }

    /**
     * 删除角色
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRoles($role, $guard)
    {
        Validator::make([
            'roles' => $role, 'guard' => $guard
        ], [
            'roles' => 'required|int',
            'guard' => 'required|string',
        ])->validate();
        $roles = Role::findById($role, $guard);
        if ($roles->name == 'root') {
            return $this->fail(__('root user cannot be deleted'));
        }
        $hasRoleUsers = User::role($roles)->get();
        collect($hasRoleUsers)->each(function ($item) use ($roles) {
            User::find($item->id)->removeRole($roles);
        });
        $allPermission = $roles->getAllPermissions();
        $roles->revokePermissionTo($allPermission);
        $roles->delete();
        return $this->success();
    }
}
