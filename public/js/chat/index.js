new Vue({
    el: '#id',
    data: {
        userId: parseInt(document.getElementById('user_id').value),
        tabPage: localStorage.getItem('tab_page') ? localStorage.getItem('tab_page') : 'userList',
        isCollapsed: false,
        pageName: pageName,
        user: {
            searchValue: '',
            listLoading: true,
            list: [],
            userModal: {
                show: false,
                rolesGuardName: 'chat',
                checkRoles: [],
                userName: '',
                permissionName: '',
                userId: 0,
                loading: false
            }
        },
        client: {
            searchValue: '',
            listLoading: true,
            list: [],
        },
        group: {
            searchValue: '',
            listLoading: true,
            list: [],
            info: {},
            groupUserShow: false,
        },
        route: {
            searchValue: '',
            listLoading: true,
            list: [],
            select: '',
        },
        roles: {
            searchValue: '',
            listLoading: true,
            list: [],
            createModal: {
                loading: false,
                show: false,
                roleName: '',
                roleGuardName: 'chat'
            },
            delModal: {
                show: false,
                loading: false,
                role: '',
                guard: 'chat'
            }
        },
        permission: {
            searchValue: '',
            list: [],
            listLoading: true,
            select: '',
            routeToPermissionLoad: false,
            assignModal: {
                show: false,
                rolesGuardName: 'chat',
                checkRoles: [],
                permissionId: 0,
                loading: false
            },
            delModal: {
                show: false,
                loading: false,
                role: '',
                guard: 'chat'
            }
        },
        allRoles: []
    },
    computed: {
        CKLColumns () {
            let col = [
                {
                    title: 'App ID',
                    key: 'app_id',
                    sortable: true
                },
                {
                    title: 'Secret ID',
                    key: 'secret_id'
                },
                {
                    title: 'Secret Key',
                    key: 'secret_key'
                },
                {
                    title: 'Expire time',
                    key: 'expire_time',
                    sortable: true
                },
                {
                    title: 'Status',
                    key: 'status',
                    slot: 'status'
                },
                {
                    title: 'Action',
                    slot: 'action',
                    width: 150,
                    align: 'center'
                }
            ];
            if (parseInt(isRoot) === 1) {
                return col
            }
            return col.filter((c, index) => index < col.length - 1)
        },
        userListColumns () {
            let col = [
                {
                    title: 'ID',
                    key: 'id',
                    sortable: true
                },
                {
                    title: 'Chat Number',
                    key: 'chat_number',
                    sortable: true
                },
                {
                    title: 'Name',
                    key: 'name',
                    sortable: true,
                    slot: 'name'
                },
                {
                    title: 'Email',
                    key: 'email'
                },
                {
                    title: 'Roles',
                    key: 'roles',
                    slot: 'roles'
                },
                {
                    title: 'Data',
                    key: 'created_at',
                    sortable: true
                },
                {
                    title: 'Action',
                    slot: 'action',
                    width: 150,
                    align: 'center'
                }
            ];
            if (parseInt(isRoot) === 1) {
                return col
            }
            return col.filter((c, index) => index < col.length - 1)
        },
        GLColumns () {
            let col = [
                {
                    title: 'ID',
                    key: 'group_id',
                    sortable: true
                },
                {
                    title: 'Name',
                    key: 'group_name'
                },
                {
                    title: 'Group number',
                    key: 'group_number',
                },
                {
                    title: 'Group manager ID',
                    key: 'user_id'
                },
                {
                    title: 'Create time',
                    key: 'created_at',
                    sortable: true
                },
                {
                    title: 'Action',
                    slot: 'action',
                    width: 150,
                    align: 'center'
                }
            ];
            return col
        },
        perListColumns () {
            let col = [
                {
                    title: 'ID',
                    key: 'id',
                    sortable: true
                },
                {
                    title: 'Name',
                    key: 'name'
                },
                {
                    title: 'Guard name',
                    key: 'guard_name'
                },
                {
                    title: 'Roles',
                    key: 'roles',
                    slot: 'roles'
                },
                {
                    title: 'Create time',
                    key: 'created_at',
                    sortable: true
                },
                {
                    title: 'Action',
                    slot: 'action',
                    width: 150,
                    align: 'center'
                }
            ];
            if (parseInt(isRoot) === 1) {
                return col
            }
            return col.filter((c, index) => index < col.length - 1)
            return col
        },
        routeListColumns () {
            let col = [
                {
                    title: 'Host',
                    key: 'host'
                },
                {
                    title: 'Method',
                    key: 'method',
                },
                {
                    title: 'Uri',
                    key: 'uri',
                },
                {
                    title: 'Name',
                    key: 'name'
                },
                {
                    title: 'Action',
                    key: 'action'
                },
                {
                    title: 'Middleware',
                    key: 'middleware'
                }
            ];
            return col
        },
        rolesListColumns () {
            let col = [
                {
                    title: 'ID',
                    key: 'id',
                    sortable: true
                },
                {
                    title: 'Name',
                    key: 'name'
                },
                {
                    title: 'Guard name',
                    key: 'guard_name',
                },
                {
                    title: 'Create time',
                    key: 'created_at',
                    sortable: true
                },
                {
                    title: 'Action',
                    slot: 'action',
                    width: 150,
                    align: 'center'
                }
            ];
            if (parseInt(isRoot) === 1) {
                return col
            }
            return col.filter((c, index) => index < col.length - 1)
            return col
        },
        createRoleRule () {
            return {
                roleName: [
                    {required: true, message: '请输入角色名', trigger: 'blur'},
                ],
                roleGuardName: [
                    {required: true, message: '请选择guard name', trigger: 'blur'},
                ]
            }
        },
        rotateIcon () {
            return [
                'menu-icon',
                this.isCollapsed ? 'rotate-icon' : ''
            ];
        },
        menuitemClasses () {
            return [
                'menu-item',
                this.isCollapsed ? 'collapsed-menu' : ''
            ]
        },
        sideWidth () {
            return (this.isCollapsed ? 78 : 200)
        }
    },
    methods: {
        setting (name) {
            if (name === 'logout') {
                http.post('/auth/logout').then(() => {
                    localStorage.clear()
                    location.href = '/auth/login'
                })
            }
        },
        setContent (name) {
            if (name === 'userList') {
                this.getUserList(1)
            }
            if (name === 'authClient') {
                this.getClientKeyList(1)
            }
            if (name === 'groupList') {
                this.getGroupList(1)
            }
            if (name === 'routeList') {
                this.getRouteList(1)
            }
            if (name === 'permissions') {
                this.getPermissionList(1)
            }
            if (name === 'rolesList') {
                this.getRolesList(1)
            }
            localStorage.setItem('tab_page', name)
        },
        exportData (type) {
            if (type === 1) {
                this.$refs.userListTable.exportCsv({
                    filename: 'UserList',
                    columns: this.userListColumns.filter((col, index) => index < this.userListColumns.length - 1),
                    data: this.user.list.data.filter((col, index) => index < this.userListColumns.length - 1)
                });
            }
            if (type === 2) {
                this.$refs.groupListTable.exportCsv({
                    filename: 'GroupList',
                    columns: this.GLColumns.filter((col, index) => index < this.GLColumns.length - 1),
                    data: this.group.list.data.filter((col, index) => index < this.GLColumns.length - 1)
                });
            }
            if (type === 3) {
                this.$refs.clientKeyListTable.exportCsv({
                    filename: 'ClientKeyList',
                    columns: this.CKLColumns.filter((col, index) => index < this.CKLColumns.length - 1),
                    data: this.client.list.data.filter((col, index) => index < this.CKLColumns.length - 1)
                });
            }
        },
        userListJump (page) {
            this.getUserList(page)
        },
        getUserList (page) {
            this.user.listLoading = true
            http.get('/api/manage/chat/all/user?page=' + page + '&keyword=' + this.user.searchValue).then((r) => {
                this.user.list = r.data.data
                this.user.listLoading = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.user.listLoading = false
            })
        },
        userListSearch (type) {
            if (type === 1 && this.user.searchValue) {
                this.getUserList(1)
            }
            if (type ===2 && this.user.searchValue) {
                this.user.searchValue = ''
                this.getUserList(1)
            }
        },
        getClientKeyList (page) {
            this.client.listLoading = true
            http.get('/api/manage/chat/key/list?page=' + page + '&keyword=' + this.client.searchValue).then((r) => {
                this.client.list = r.data.data
                this.client.listLoading = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.client.listLoading = false
            })
        },
        clientKeyListJump (page) {
            this.getClientKeyList(page)
        },
        clientKeyListSearch (type) {
            if (type === 1 && this.client.searchValue) {
                this.getClientKeyList(1)
            }
            if (type ===2 && this.client.searchValue) {
                this.client.searchValue = ''
                this.getClientKeyList(1)
            }
        },
        getGroupList (page) {
            this.group.listLoading = true
            http.get('/api/manage/chat/all/group?page=' + page + '&keyword=' + this.group.searchValue).then((r) => {
                this.group.list = r.data.data
                this.group.listLoading = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.group.listLoading = false
            })
        },
        groupListJump (page) {
            this.getGroupList(page)
        },
        groupListSearch (type) {
            if (type === 1 && this.group.searchValue) {
                this.getGroupList(1)
            }
            if (type ===2 && this.group.searchValue) {
                this.group.searchValue = ''
                this.getGroupList(1)
            }
        },
        showGroupInfo (groupId) {
            this.group.groupUserShow = true
            http.get('/api/chat/member/group/' + groupId).then((r) => {
                this.group.info = r.data.data
            })
        },
        getPermissionList (page) {
            this.permission.listLoading = true
            http.get('/api/manage/permission/list?page=' + page + '&keyword=' + this.permission.searchValue + '&s=' + this.permission.select).then((r) => {
                this.permission.list = r.data.data
                this.permission.listLoading = false
                this.permission.routeToPermissionLoad =false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.permission.listLoading = false
                this.permission.routeToPermissionLoad = false
            })
        },
        permissionsListSearch (type) {
            if (type === 1 && (this.permission.searchValue || this.permission.select)) {
                this.getPermissionList(1)
            }
            if (type ===2 && (this.permission.searchValue || this.permission.select)) {
                this.permission.searchValue = ''
                this.permission.select = ''
                this.getPermissionList(1)
            }
        },
        permissionsListJump (page) {
            this.getPermissionList(page)
        },
        getRouteList (page) {
            this.route.listLoading = true
            http.get('/api/manage/route/list?page=' + page + '&keyword=' + this.route.searchValue + '&s=' + this.route.select).then((r) => {
                this.route.list = r.data.data
                this.route.listLoading = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.route.listLoading = false
            })
        },
        routeListJump (page) {
            this.getRouteList(page)
        },
        routeListSearch (type) {
            if (type === 1 && this.route.searchValue) {
                this.getRouteList(1)
            }
            if (type ===2 && this.route.searchValue) {
                this.route.searchValue = ''
                this.getRouteList(1)
            }
        },
        getRolesList (page) {
            this.roles.listLoading = true
            http.get('/api/manage/permission/role/list?page=' + page + '&keyword=' + this.roles.searchValue).then((r) => {
                this.roles.list = r.data.data
                this.roles.listLoading = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.roles.listLoading = false
            })
        },
        rolesListJump (page) {
            this.getRolesList(page)
        },
        rolesListSearch (type) {
            if (type === 1 && this.roles.searchValue) {
                this.getRolesList(1)
            }
            if (type ===2 && this.roles.searchValue) {
                this.roles.searchValue = ''
                this.getRolesList(1)
            }
        },
        routeToPermission () {
            this.permission.listLoading = true
            this.permission.routeToPermissionLoad = true
            http.post('/api/manage/permission/route/set').then((r) => {
                this.getPermissionList(1)
                this.$Message.success({
                    content: 'success',
                    duration: 3
                });
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.permission.listLoading = false
                this.permission.routeToPermissionLoad = false
            })
        },
        createRoles (name) {
            this.$refs[name].validate((valid) => {
                if (valid) {
                    this.roles.createModal.loading = true;
                    http.post('/api/manage/permission/role/create', Qs.stringify({
                        roles: this.roles.createModal.roleName,
                        guard: this.roles.createModal.roleGuardName,
                    })).then((r) => {
                        this.getRolesList(1)
                        this.$Message.success({
                            content: 'success',
                            duration: 3
                        });
                        this.roles.createModal.loading = false;
                        this.roles.createModal.show = false
                        this.roles.createModal.roleName = ''
                    }).catch((e) => {
                        this.$Message.error({
                            content: e.response.data.data,
                            duration: 3
                        });
                        this.roles.createModal.loading = false;
                        this.roles.createModal.show = false
                        this.roles.createModal.roleName = ''
                    })
                } else {
                    this.$Notice.error({
                        title: '错误提醒',
                        desc: '填写格式有误'
                    })
                }
            })
        },
        getAllRoles (id) {
            http.get('/api/manage/permission/role/all?guard=chat&permission=' + id).then((r) => {
                this.allRoles = r.data.data
                if (this.allRoles.length > 0) {
                    let roles = [];
                    for (let i = 0; i < this.allRoles.length; i++) {
                        if (this.allRoles[i].have) {
                            roles.push(this.allRoles[i].name)
                        }
                    }
                    this.permission.assignModal.checkRoles = roles
                }
                this.permission.assignModal.permissionId = id
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
            })
        },
        assignRoles () {
            if (this.permission.assignModal.checkRoles.length > 0 && this.permission.assignModal.permissionId > 0) {
                this.permission.assignModal.loading = true;
                http.post('/api/manage/permission/give/role', Qs.stringify({
                    permission: this.permission.assignModal.permissionId,
                    roles: this.permission.assignModal.checkRoles,
                    guard: this.permission.assignModal.rolesGuardName,
                })).then((r) => {
                    this.$Message.success({
                        content: 'success',
                        duration: 3
                    });
                    this.permission.assignModal.loading = false;
                    this.permission.assignModal.show = false
                    this.getPermissionList(1)
                }).catch((e) => {
                    this.$Message.error({
                        content: e.response.data.data,
                        duration: 3
                    });
                    this.permission.assignModal.loading = false;
                    this.permission.assignModal.show = false
                })
            } else {
                this.$Message.error({
                    content: '参数错误',
                    duration: 3
                });
            }
        },
        giveRoleToUser () {
            if (this.user.userModal.checkRoles.length > 0 && this.user.userModal.userId > 0) {
                this.user.userModal.loading = true;
                http.post('/api/manage/permission/role/give/user', Qs.stringify({
                    uid : this.user.userModal.userId,
                    roles: this.user.userModal.checkRoles,
                    guard: this.user.userModal.rolesGuardName,
                })).then((r) => {
                    this.$Message.success({
                        content: 'success',
                        duration: 3
                    });
                    this.user.userModal.loading = false;
                    this.user.userModal.show = false
                    this.getUserList(1)
                }).catch((e) => {
                    this.$Message.error({
                        content: e.response.data.data,
                        duration: 3
                    });
                    this.user.userModal.loading = false;
                    this.user.userModal.show = false
                })
            } else {
                this.$Message.error({
                    content: '参数错误',
                    duration: 3
                });
            }
        },
        deleteRoles () {
            this.roles.delModal.loading = true
            console.log(this.roles.delModal)
            http.delete('/api/manage/permission/role/' + this.roles.delModal.role + '/' + this.roles.delModal.guard + '/delete').then((r) => {
                this.roles.delModal.loading = false
                this.getRolesList(1)
                this.$Message.success({
                    content: 'success',
                    duration: 3
                });
                this.roles.delModal.show = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.roles.delModal.loading = false
                this.roles.delModal.show = false
            })
        },
        deletePermission () {
            this.permission.delModal.loading = true
            http.delete('/api/manage/permission/' + this.permission.delModal.role + '/' + this.permission.delModal.guard + '/delete').then((r) => {
                this.permission.delModal.loading = false
                this.getRolesList(1)
                this.$Message.success({
                    content: 'success',
                    duration: 3
                });
                this.permission.delModal.show = false
            }).catch((e) => {
                this.$Message.error({
                    content: e.response.data.data,
                    duration: 3
                });
                this.permission.delModal.loading = false
                this.permission.delModal.show = false
            })
        },
        collapsedSider () {
            this.$refs.side.toggleCollapse();
        },
        selectMenu (name) {
            location.href = '/' + name
        }
    },
    created () {
        this.setContent(this.tabPage)
    }
})
