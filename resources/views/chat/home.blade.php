@extends('chat.common.app')
@section('style')
    <style>
        .m-ui-content {
            width: 100%;
            position: relative;
            height: 100%;
        }
        .i-content {
            height: calc(100% - 133px);
        }
        .m-ui-head-right {
            float: right;
            height: 100%;
            text-align: center;
        }
        .m-ui-head-right > div {
            padding: 0 10px;
        }
        .i-tab-box {
            padding: 5px 20px;
        }
        .i-search-con {
            padding: 10px 0;
        }
        .i-search-col {
            display: inline-block;
            width: 200px;
        }
        .i-search-input {
            display: inline-block;
            width: 200px;
            margin-left: 2px;
        }
        .m-ui-content-group-user {
            position: relative;
            overflow-y: auto;
            max-height: calc(100vh - 250px);
            min-height: 150px;
        }
        .m-ui-g-user {
            float: left;
            width: 25%;
            text-align: center;
        }
        .m-ui-gu-row {
            padding: 6px 0;
        }
        .m-ui-gu-gi {
            margin-bottom: 10px;
        }
        .m-ui-content-group-user span {
            vertical-align: middle;
            text-align: center;
            display: block;
        }
    </style>
@endsection
@section('content')
    <div id="id" class="m-ui-content">
        <input type="hidden" value="{{ $user->id }}" id="user_id">
        <Layout style="height: 100%">
            <i-header>
                <div class="m-ui-head-left">

                </div>
                <div class="m-ui-head-right">
                    <div style="height: 100%;float: left;">
                        <img src="{{ asset($user->photo) }}" width="40" height="40" style="border-radius: 40px;margin-top: 10px">
                    </div>
                    <Dropdown @on-click="setting">
                        <a href="javascript:void(0)">
                            {{ $user->name }}
                            <Icon type="ios-arrow-down"></Icon>
                        </a>
                        <dropdown-menu slot="list">
                            <dropdown-item name="logout">退出登录</dropdown-item>
                        </dropdown-menu>
                    </Dropdown>
                </div>
            </i-header>
            <i-content class="i-content">
                <Tabs :value="tabPage" class="i-tab-box" @on-click="setContent">
                    <tab-pane label="用户" name="userList">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    {{--<i-select v-model="searchKey" class="i-search-col">
                                        <i-option :value="111">111</i-option>
                                    </i-select>--}}
                                    <i-input clearable placeholder="输入关键字搜索" class="i-search-input" v-model="user.searchValue" ></i-input>
                                    <i-button @click="userListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="userListSearch(2)" class="search-btn" type="success">重置</i-button>
                                </div>
                                <div>
                                    <i-table :loading="user.listLoading" :columns="userListColumns" :data="user.list.data" ref="userListTable">
                                        <template slot-scope="{ row }" slot="name">
                                            <strong>@{{ row.name }}</strong>
                                        </template>
                                        <template slot-scope="{ row }" slot="roles">
                                            <Tag v-if="row.roles.length" v-for="role in row.roles" :key="role" color="cyan">@{{ role }}</Tag>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="primary" size="small" @click="user.userModal.show = true;user.userModal.checkRoles = row.roles;user.userModal.userName = row.name;user.userModal.userId = row.id; getAllRoles(0)"
                                                      style="margin-right: 5px">权限</i-button>
                                        </template>
                                    </i-table>
                                    <br>
                                    <i-button type="primary" size="large" @click="exportData(1)"><Icon type="ios-download-outline"></Icon> Export source data</i-button>
                                    <br>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="userListJump" :total="user.list.total" :current="user.list.current_page" :page-size="user.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                        <Modal v-model="user.userModal.show" :mask-closable="false" footer-hide>
                            <p slot="header" style="text-align: center;">角色授权</p>
                            <i-form :label-width="100" v-if="allRoles.length">
                                <form-item label="User name">
                                    <i-input v-model="user.userModal.userName" disabled></i-input>
                                </form-item>
                                <form-item label="Guard name">
                                    <i-select v-model="user.userModal.rolesGuardName" class="i-search-col">
                                        <i-option value="chat">chat</i-option>
                                    </i-select>
                                </form-item>
                                <form-item label="Roles">
                                    <checkbox-group v-model="user.userModal.checkRoles">
                                        <Checkbox :label="role.name" v-for="role in allRoles" :key="role.id" :disabled="role.name === 'root'"></Checkbox>
                                    </checkbox-group>
                                </form-item>
                                <form-item>
                                    <i-button type="primary" :loading="user.userModal.loading" @click="giveRoleToUser">
                                        <span v-if="!user.userModal.loading">Assign</span>
                                        <span v-else>Loading...</span>
                                    </i-button>
                                </form-item>
                            </i-form>
                            <div v-else style="width: 100%;min-height: 200px"><Spin fix></Spin></div>
                        </Modal>
                    </tab-pane>
                    <tab-pane label="群" name="groupList">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    {{--<i-select v-model="searchKey" class="i-search-col">
                                        <i-option :value="111">111</i-option>
                                    </i-select>--}}
                                    <i-input clearable placeholder="搜索Group number,Name" class="i-search-input" v-model="group.searchValue" ></i-input>
                                    <i-button @click="groupListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="groupListSearch(2)" class="search-btn" type="success">重置</i-button>
                                </div>
                                <div>
                                    <i-table :loading="group.listLoading" :columns="GLColumns" :data="group.list.data" ref="groupListTable">
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="success" @click="showGroupInfo(row.group_id)" size="small">查看成员</i-button>
                                        </template>
                                    </i-table>
                                    <br>
                                    <i-button type="primary" size="large" @click="exportData(2)"><Icon type="ios-download-outline"></Icon> Export source data</i-button>
                                    <br>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="groupListJump" :total="group.list.total" :current="group.list.current_page" :page-size="group.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                        <Modal v-model="group.groupUserShow" :mask-closable="false" :styles="{top: '50px'}" footer-hide>
                            <p slot="header" style="text-align: center;">群信息</p>
                            <div class="m-ui-content-group-user" v-if="group.info.group_id">
                                <div class="m-ui-gu-gi">
                                    <Row class="m-ui-gu-row">
                                        <i-col span="8">群名称</i-col><i-col span="16">@{{group.info.group_name}}</i-col>
                                    </Row>
                                    <Row class="m-ui-gu-row">
                                        <i-col span="8">群号</i-col><i-col span="16">@{{group.info.group_number}}</i-col>
                                    </Row>
                                    <Row class="m-ui-gu-row">
                                        <i-col span="8">群主</i-col><i-col span="16">@{{group.info.group_owner_name}}</i-col>
                                    </Row>
                                    <Row class="m-ui-gu-row">
                                        <i-col span="8">创建时间</i-col><i-col span="16">@{{group.info.created_at}}</i-col>
                                    </Row>
                                </div>
                                <Divider orientation="left" size="small">群成员</Divider>
                                <div class="m-ui-g-user" v-if="group.info.group_members" v-for="item in group.info.group_members" :key="item.group_user_id">
                                    <img :src="item.photo" width="50" height="50"><span>@{{item.group_user_name}}</span>
                                </div>
                            </div>
                            <div class="m-ui-content-group-user" v-else><Spin fix></Spin></div>
                        </Modal>
                    </tab-pane>
                    <tab-pane label="客户端授权" name="authClient">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    {{--<i-select v-model="searchKey" class="i-search-col">
                                        <i-option :value="111">111</i-option>
                                    </i-select>--}}
                                    <i-input clearable placeholder="输入Secret ID关键字搜索" class="i-search-input" v-model="client.searchValue" ></i-input>
                                    <i-button @click="clientKeyListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="clientKeyListSearch(2)" class="search-btn" type="success">重置</i-button>
                                </div>
                                <div>
                                    <i-table :loading="client.listLoading" :columns="CKLColumns" :data="client.list.data" ref="clientKeyListTable">
                                        <template slot-scope="{ row, index }" slot="status">
                                            <i-button v-if="row.status === 1" type="error" size="small">已过期</i-button>
                                            <i-button v-else type="success" size="small">可使用</i-button>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="error" size="small">Cancel</i-button>
                                        </template>
                                    </i-table>
                                    <br>
                                    <i-button type="primary" size="large" @click="exportData(3)"><Icon type="ios-download-outline"></Icon> Export source data</i-button>
                                    <br>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="clientKeyListJump" :total="client.list.total" :current="client.list.current_page" :page-size="client.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </tab-pane>
                    <tab-pane label="权限管理" name="permissions">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    <i-select v-model="permission.select" class="i-search-col">
                                        <i-option value="all">All</i-option>
                                        <i-option value="protected">Protected</i-option>
                                        <i-option value="public">Public</i-option>
                                    </i-select>
                                    <i-input clearable placeholder="输入Name关键字搜索" class="i-search-input" v-model="permission.searchValue" ></i-input>
                                    <i-button @click="permissionsListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="permissionsListSearch(2)" class="search-btn" type="success">重置</i-button>
                                    <i-button @click="routeToPermission" :loading="permission.routeToPermissionLoad" class="search-btn" type="success">更新路由权限</i-button>
                                </div>
                                <div>
                                    <i-table :loading="permission.listLoading" :columns="perListColumns" :data="permission.list.data" ref="permissionsListTable">
                                        <template slot-scope="{ row }" slot="roles">
                                            <Tag v-if="row.roles.length" v-for="role in row.roles" :key="role" color="green">@{{ role }}</Tag>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="success" @click="permission.assignModal.show = true ;allRoles = [];permission.assignModal.permissionName = row.name; getAllRoles(row.id)" size="small">Assign</i-button>
                                            <i-button type="error" @click="permission.delModal.role = row.id; permission.delModal.guard = row.guard_name; permission.delModal.show = true" size="small">Del</i-button>
                                        </template>
                                    </i-table>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="permissionsListJump" :total="permission.list.total" :current="permission.list.current_page" :page-size="permission.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                        <Modal v-model="permission.assignModal.show" :mask-closable="false" footer-hide>
                            <p slot="header" style="text-align: center;">授权</p>
                            <i-form :label-width="100" v-if="allRoles.length">
                                <form-item label="Permission name">
                                    <i-input v-model="permission.assignModal.permissionName" disabled></i-input>
                                </form-item>
                                <form-item label="Guard name">
                                    <i-select v-model="permission.assignModal.rolesGuardName" class="i-search-col">
                                        <i-option value="chat">chat</i-option>
                                    </i-select>
                                </form-item>
                                <form-item label="Roles">
                                    <checkbox-group v-model="permission.assignModal.checkRoles">
                                        <Checkbox :label="role.name" v-for="role in allRoles" :key="role.id" :disabled="role.name === 'root'"></Checkbox>
                                    </checkbox-group>
                                </form-item>
                                <form-item>
                                    <i-button type="primary" :loading="permission.assignModal.loading" @click="assignRoles">
                                        <span v-if="!permission.assignModal.loading">Assign</span>
                                        <span v-else>Loading...</span>
                                    </i-button>
                                </form-item>
                            </i-form>
                            <div v-else style="width: 100%;min-height: 200px"><Spin fix></Spin></div>
                        </Modal>
                        <Modal v-model="permission.delModal.show" width="360">
                            <p slot="header" style="color:#f60;text-align:center">
                                <Icon type="ios-information-circle"></Icon>
                                <span>Delete confirmation</span>
                            </p>
                            <div style="text-align:center">
                                <p>权限删除后，无法找回，且拥有该权限的用户权限也会被解除</p>
                                <p>您确定要删除？</p>
                            </div>
                            <div slot="footer">
                                <i-button type="error" size="large" long :loading="permission.delModal.loading" @click="deletePermission">Delete</i-button>
                            </div>
                        </Modal>
                    </tab-pane>
                    <tab-pane label="路由列表" name="routeList">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    <i-select v-model="route.select" class="i-search-col">
                                        <i-option value="GET|HEAD">GET|HEAD</i-option>
                                        <i-option value="POST">POST</i-option>
                                        <i-option value="DELETE">DELETE</i-option>
                                    </i-select>
                                    <i-input clearable placeholder="输入Name关键字搜索" class="i-search-input" v-model="route.searchValue" ></i-input>
                                    <i-button @click="routeListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="routeListSearch(2)" class="search-btn" type="success">重置</i-button>
                                </div>
                                <div>
                                    <i-table :loading="route.listLoading" :columns="routeListColumns" :data="route.list.data" ref="routeListTable">
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="error" size="small">Cancel</i-button>
                                        </template>
                                    </i-table>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="routeListJump" :total="route.list.total" :current="route.list.current_page" :page-size="route.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </tab-pane>
                    <tab-pane label="角色管理" name="rolesList">
                        <Card style="width: 100%">
                            <div>
                                <div class="i-search-con i-search-con-top">
                                    <i-input clearable placeholder="输入Name关键字搜索" class="i-search-input" v-model="roles.searchValue" ></i-input>
                                    <i-button @click="rolesListSearch(1)" class="search-btn" type="primary">搜索</i-button>
                                    <i-button @click="rolesListSearch(2)" class="search-btn" type="success">重置</i-button>
                                    <i-button @click="roles.createModal.show = true" class="search-btn" type="success">新建角色</i-button>
                                </div>
                                <div>
                                    <i-table :loading="roles.listLoading" :columns="rolesListColumns" :data="roles.list.data" ref="rolesListTable">
                                        <template slot-scope="{ row, index }" slot="action">
                                            <i-button type="error" @click="roles.delModal.role = row.id; roles.delModal.guard = row.guard_name; roles.delModal.show = true" size="small">Del</i-button>
                                        </template>
                                    </i-table>
                                    <div style="text-align: right;padding: 10px 0;">
                                        <Page @on-change="rolesListJump" :total="roles.list.total" :current="roles.list.current_page" :page-size="roles.list.per_page" show-elevator show-total />
                                    </div>
                                </div>
                            </div>
                        </Card>
                        <Modal v-model="roles.createModal.show" :mask-closable="false" footer-hide>
                            <p slot="header" style="text-align: center;">创建角色</p>
                            <i-form ref="createRoleForm" :model="roles.createModal" :rules="createRoleRule" :label-width="100">
                                <form-item prop="roleName" label="角色名">
                                    <i-input v-model="roles.createModal.roleName" placeholder="请角色名"></i-input>
                                </form-item>
                                <form-item prop="roleGuardName" label="Guard name">
                                    <i-select v-model="roles.createModal.roleGuardName" class="i-search-col">
                                        <i-option value="chat">chat</i-option>
                                    </i-select>
                                </form-item>
                                <form-item>
                                    <i-button type="primary" :loading="roles.createModal.loading" @click="createRoles('createRoleForm')">
                                        <span v-if="!roles.createModal.loading">Create</span>
                                        <span v-else>Loading...</span>
                                    </i-button>
                                </form-item>
                            </i-form>
                        </Modal>
                        <Modal v-model="roles.delModal.show" width="360">
                            <p slot="header" style="color:#f60;text-align:center">
                                <Icon type="ios-information-circle"></Icon>
                                <span>Delete confirmation</span>
                            </p>
                            <div style="text-align:center">
                                <p>角色删除后，无法找回，且拥有该角色的用户权限也会被解除</p>
                                <p>您确定要删除？</p>
                            </div>
                            <div slot="footer">
                                <i-button type="error" size="large" long :loading="roles.delModal.loading" @click="deleteRoles">Delete</i-button>
                            </div>
                        </Modal>
                    </tab-pane>
                </Tabs>
            </i-content>
            <i-footer>
                <p style="text-align: center">Copyright © 2018-{{ date('Y') }} Mister Pan.</p>
            </i-footer>
        </Layout>
    </div>
@endsection
@section('script')
    <script>
        var isRoot = '{{ $isRoot }}'
    </script>
    <script src="/js/chat/index.js?v={{rand(1,99)}}"></script>
@endsection