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
    'middleware' => ['api', 'api.throttle'],
    'as' => 'api'
], function ($api) {
    $api->group(['prefix' => 'auth', 'as' => 'auth'], function ($api) {
        $api->post('register', 'RegisterController@register')->name('.register');
//        $api->post('login', 'LoginController@login')->name('api.auth.login');
        $api->post('login', 'AuthController@login')->name('.login');
        $api->post('logout', 'AuthController@logout')->name('.logout');
        $api->post('refresh', 'AuthController@refresh')->name('.refresh');
        $api->post('me', 'AuthController@me')->name('.me');
    });
    $api->group(['prefix' => 'lib', 'as' => 'lib'], function ($api) {
        $api->post('test', 'LoginController@test')->name('.test'); // 调试路由
        $api->get('getExpression', 'ExpressionController@expression'); // 获取表情包
        $api->post('ping', 'HeartbeatController@ping')->middleware('refreshToken')->name('.ping'); // 心跳检测 参数 ping
    });
    $api->group([
        'prefix' => 'chat',
        'middleware' => 'auth:api',
        'where' => ['group_id' => '[\d]+', 'limit' => '[\d]*', 'friend_id' => '[\d]+'],
        'as' => 'chat'
    ], function ($api) {
        // 群相关路由
        $api->get('getGroupMessage/{group_id}/{limit?}', 'Chat\ChatGroupMessageController@getGroupMessage')->name('.getGroupMes'); // 通过群ID获取群消息 参数 group_id
        $api->post('joinGroup', 'Chat\ChatGroupUserController@joinGroup')->name('.joinGroup'); // 登录用户加入群 参数 group_id
        $api->get('getGroupMember/{group_id}', 'Chat\ChatGroupController@getGroupMember')->name('.getGroupMember'); // 获取群成员 参数 group_id
        $api->post('createGroup', 'Chat\ChatGroupController@createGroup')->name('.createGroup');
        // 用户相关路由
        $api->get('getFriendsList', 'Chat\UserController@getFriendsList')->name('.getFriendsList'); // 获取好友列表 无参数
        $api->get('isFriends/{friend_id}', 'Chat\ChatUsersController@isFriends')->name('.isFriends'); // 是否是好友 参数 friend_id
        $api->post('becomeFriends', 'Chat\ChatUsersController@becomeFriends')->name('.becomeFriends'); // 添加好友 参数 friend_id
        $api->get('getGroupList', 'Chat\UserController@getGroupList')->name('.getGroupList'); // 获取登录用户的群列表 无参数
        $api->get('getUserChatMessage', 'Chat\ChatUsersMessageController@getUserChatMessage')->name('.getChatMessage'); // 获取登录用户对应好友的消息 参数 friend_id
    });
});
