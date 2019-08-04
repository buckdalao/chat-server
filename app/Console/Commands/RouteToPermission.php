<?php

namespace App\Console\Commands;

use App\Libs\RouteToPermission as RouteList;
use App\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RouteToPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:toPermission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set route list to permission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createRoot();
        $permission = new RouteList();
        $permission->create();
        $list = Permission::all();
        $superAdminRoles = Role::findByName('root', 'chat');
        if ($superAdminRoles) {
            $superAdminRoles->givePermissionTo($list);
        } else {
            $this->error('There are no root roles');
            exit;
        }
        echo "\033[0;32mSuccess\033[0m" . PHP_EOL;
    }

    protected function createRoot()
    {
        $user = User::find(1);
        if ($user) {
            $role = Role::findOrCreate('root', 'chat');
            $user->assignRole($role);
        } else {
            $this->error('There are no ones with user ID 1');
            exit;
        }
    }
}
