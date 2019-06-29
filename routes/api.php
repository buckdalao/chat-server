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
    'middleware' => ['api', 'api.throttle', 'clientAuth'],
    'as' => 'api'
], function ($api) {
    $api->group(['prefix' => 'auth', 'as' => 'auth'], function ($api) {
        $api->post('register', 'RegisterController@register')->name('.register');
//        $api->post('login', 'LoginController@login')->name('api.auth.login');
        $api->post('login', 'AuthController@login')->name('.login');
        $api->post('logout', 'AuthController@logout')->name('.logout');
        $api->post('refresh', 'AuthController@refresh')->name('.refresh');
        $api->post('me', 'AuthController@me')->name('.me');
        // 更新用户信息 param: email name phone
        $api->post('information/update', 'AuthController@updateInformation')->middleware('auth:api')->name('.updateInformation');
        // 修改密码 param: old_password & password & password_confirmation
        $api->post('password/change', 'AuthController@changePassword')->middleware('auth:api')->name('.changePassword');
    });
    $api->group(['prefix' => 'lib', 'as' => 'lib'], function ($api) {
        $api->post('test', 'LoginController@test')->name('.test'); // 调试路由
        $api->get('getExpression', 'ExpressionController@expression'); // 获取表情包
        $api->post('ping', 'HeartbeatController@ping')->middleware('refreshToken')->name('.ping'); // 心跳检测 参数 ping
    });
    $api->group([
        'prefix' => 'chat',
        'middleware' => 'auth:api',
        'where' => ['group_id' => '[\d]+', 'limit' => '[\d]*', 'friend_id' => '[\d]+', 'uid' => '[\d]+', 'chat_id' => '[\d]+', 'apply_id' => '[\d]+'],
        'as' => 'chat',
        'namespace' => 'Chat'
    ], function ($api) {
        // 群相关路由
        $api->get('getGroupMessage/{group_id}/{limit?}', 'ChatGroupMessageController@getGroupMessage')->name('.getGroupMes'); // 通过群ID获取群消息 参数 group_id
        $api->get('getGroupMember/{group_id}', 'ChatGroupController@getGroupMember')->name('.getGroupMember'); // 获取群成员 参数 group_id
        $api->post('createGroup', 'ChatGroupController@createGroup')->name('.createGroup');
        // 用户相关路由
        $api->get('friendsList/get', 'UserController@getFriendsList')->name('.getFriendsList'); // 获取好友列表 无参数
        $api->get('isFriends/{friend_id}', 'ChatUsersController@isFriends')->name('.isFriends'); // 是否是好友 参数 friend_id
        $api->get('groupList/get', 'UserController@getGroupList')->name('.getGroupList'); // 获取登录用户的群列表 无参数

        $api->get('getChatMessage/chat/{chat_id}/{limit?}', 'ChatUsersMessageController@getChatMessageByChatId')
            ->name('.getChatMessageByChatId'); // 获取登录用户对应好友的消息 参数 chat_id

        $api->get('getChatMessage/u/{friend_id}/{limit?}', 'ChatUsersMessageController@getChatMessageByUid')
            ->name('.getChatMessageByUid'); // 获取登录用户对应好友的消息 参数 friend_id
        $api->get('getUserInfo/{uid}', 'UserController@getUserInfo')->name('.getUserInfo'); // 获取用户信息
        $api->post('addFriends', 'ChatApplyController@addFriends')->name('.addFriends'); // 添加群或好友 param: friend_id | group_id & remarks
        $api->post('searchNo', 'ChatToolController@searchNo')->name('.searchNo'); // 搜索好友和群 param: chat_number
        $api->post('apply/audit/{apply_id}', 'ChatApplyController@audit')->name('.applyFriendAudit'); // 加群加好友审核 param: audit
        $api->get('apply/get', 'ChatApplyController@getApplyList')->name('.getApplyList'); // 获取好友和群申请列表 无参数
        $api->get('apply/notify/reset', 'ChatApplyController@resetNotifyBadge')->name('.resetNotifyBadge'); // 重置申请的消息提醒  无参数

        $api->post('init', 'ChatController@init')->name('.init'); // websocket 初始化 param: connect_id
        $api->post('chatMessage', 'ChatController@onChatMessage')->name('.chatMessage'); // 对话消息接口 param: chat_id & content
        $api->post('groupMessage', 'ChatController@onGroupMessage')->name('.groupMessage'); // 群消息接口 param: group_id & content
        $api->get('connectClose', 'ChatController@onConnectClose')->name('.connectClose'); // websocket断开接口
        $api->post('resetBadge', 'ChatMessageBadgeController@resetBadge')->name('.resetBadge'); // 重置消息提醒 param:chat_id or group id & is_group
    });
    $api->group([
        'prefix' => 'media',
        'middleware' => 'auth:api',
        'where' => ['group_id' => '[\d]+', 'chat_id' => '[\d]+'],
        'as' => 'chat.resources',
        'namespace' => 'Chat\Util'
    ], function ($api) {
        $api->post('upload/recorder/chat/{chat_id}', 'UploadController@uploadRecorderByChat')->name('.uploadRecorderByChat');
        $api->post('upload/recorder/group/{group_id}', 'UploadController@uploadRecorderByGroup')->name('.uploadRecorderByGroup');
        $api->post('upload/imgToBase64', 'UploadController@imgToBase64')->name('.imgToBase64');
    });
});
