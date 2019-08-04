<?php

namespace App\Http\Controllers\Api\Permission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createSuperAdmin(Request $request)
    {
        if ($request->user()->id != 1) {
            return $this->fail('You have no right');
        }
        $role = Role::findOrCreate('root', 'chat');
        $request->user()->assignRole($role);
        return $this->success();
    }

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

    public function assignRoleToUser(Request $request)
    {
        Validator::make($request->all(), [
            'uid' => 'required|string',
        ])->validate();
    }

    public function roleList(Request $request)
    {
        if ($request->get('keyword')) {
            $list = Role::query()->where('name', 'like', "%".$request->get('keyword'))->paginate(15);
        } else {
            $list = Role::query()->paginate(15);
        }
        return $this->successWithData($list);
    }
}
