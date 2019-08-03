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
        $api->post('super/admin/create', 'RolesController@createSuperAdmin')->name('.createSuperAdmin'); // 创建超管用户
        $api->post('route/set', 'PermissionController@routeToPermission')->name('.routeToPermission'); // 将所有路由写入到权限列表
    });
    $api->group([
        'prefix' => 'route',
        'as' => 'chat.route',
    ], function ($api) {
        $api->get('list', 'Util\RouteController@routeList')->name('.routeList');
    });
});
