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

$api->version(['v1'],[
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['api', 'api.throttle'],
], function ($api) {
    $api->group(['prefix' => 'auth'], function ($api) {
        $api->post('register', 'RegisterController@register')->name('api.auth.register');
//        $api->post('login', 'LoginController@login')->name('api.auth.login');
        $api->post('login', 'AuthController@login')->name('api.auth.login');
        $api->post('logout', 'AuthController@logout')->name('api.auth.logout');
        $api->post('refresh', 'AuthController@refresh')->name('api.auth.refresh');
        $api->post('me', 'AuthController@me')->name('api.auth.me');
    });
    $api->group(['prefix' => 'lib'], function ($api) {
        $api->post('test', 'LoginController@test'); // 调试路由
        $api->get('getExpression', 'ExpressionController@expression'); // 获取表情包
        $api->post('ping', 'HeartbeatController@ping')->middleware('refreshToken')->name('api.lib.ping'); // 心跳检测 参数 ping
    });
    $api->group(['prefix' => 'chat', 'middleware' => 'auth:api'], function ($api) {
        // 群相关路由
        $api->get('getGroupMes', 'Chat\ChatGroupMessageController@getCroupMes')->name('api.chat.getGroupMes'); // 通过群ID获取群消息 参数 group_id
        $api->post('joinGroup', 'Chat\ChatGroupUserController@joinGroup')->name('api.chat.joinGroup'); // 登录用户加入群 参数 group_id
        $api->get('getGroupMember', 'Chat\ChatGroupController@getGroupMember')->name('api.chat.getGroupMember'); // 获取群成员 参数 group_id
        // 用户相关路由
        $api->get('getFriendsList', 'Chat\UserController@getFriendsList')->name('api.chat.getFriendsList'); // 获取好友列表 无参数
        $api->get('isFriends', 'Chat\ChatUsersController@isFriends')->name('api.chat.isFriends'); // 是否是好友 参数 friend_id
        $api->post('becomeFriends', 'Chat\ChatUsersController@becomeFriends')->name('api.chat.becomeFriends'); // 添加好友 参数 friend_id
        $api->get('getGroupList', 'Chat\UserController@getGroupList')->name('api.chat.getGroupList'); // 获取登录用户的群列表 无参数
        $api->get('getChatMessage', 'Chat\ChatUsersMessageController@getChatMessage')->name('api.chat.getChatMessage'); // 获取登录用户对应好友的消息 参数 friend_id
    });
});
