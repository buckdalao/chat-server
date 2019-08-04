<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version(['v1'], [
    'namespace'  => 'App\Http\Controllers\Api',
    'middleware' => ['api', 'api.throttle', 'client.auth', 'auth:api'],
    'prefix' => 'api/manage',
    'as' => 'api'
], function ($api) {
    $api->group([
        'prefix' => 'permission',
        'where' => ['group_id' => '[\d]+', 'chat_id' => '[\d]+'],
        'as' => 'chat.permissions',
        'namespace' => 'Permission'
    ], function ($api) {
        $api->post('super/admin/create', 'RolesController@createSuperAdmin')->name('.createSuperAdmin.protected'); // 创建超管用户
        $api->post('route/set', 'PermissionController@routeToPermission')->name('.routeToPermission.protected'); // 将所有路由写入到权限列表
        $api->get('list', 'PermissionController@permissionList')->name('.list.protected'); // 将所有路由写入到权限列表
        $api->get('role/list', 'RolesController@roleList')->name('.roleList.protected'); // 将所有路由写入到权限列表
    });
    $api->group([
        'prefix' => 'route',
        'as' => 'chat.route',
    ], function ($api) {
        $api->get('list', 'Util\RouteController@routeList')->name('.routeList.protected');
    });
    $api->group([
        'prefix' => 'chat',
        'where' => ['group_id' => '[\d]+', 'limit' => '[\d]*', 'friend_id' => '[\d]+', 'uid' => '[\d]+', 'chat_id' => '[\d]+', 'apply_id' => '[\d]+'],
        'as' => 'chat',
        'namespace' => 'Chat'
    ], function ($api) {
        $api->get('/all/user', 'UserController@getAllUsers')->name('.getAllUsers.protected'); // 获取所有用户列表  支持参数keyword模糊查询 带分页信息
        $api->get('/all/group', 'ChatGroupController@getAllGroupList')->name('.getAllGroupList.protected'); // 获取所有群列表  支持参数keyword模糊查询 带分页信息
        $api->get('/key/list', 'Util\ClientKeyController@getClientAuthList')->name('.getClientAuthList.protected');// 获取所有client key列表  支持参数keyword模糊查询 带分页信息
    });
});
