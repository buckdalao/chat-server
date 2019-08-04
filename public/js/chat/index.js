new Vue({
    el: '#id',
    data: {
        userId: parseInt(document.getElementById('user_id').value),
        searchValue: '',
        userListLoading: true,
        userList: [],
        searchValueCL: '',
        CKLLoading: true,
        clientKeyList: [],
        searchValueGL: '',
        GLLoading: true,
        groupList: [],
        group: {},
        groupUserShow: false,
        permissionKeyword: '',
        permissionsList: [],
        perListLoading: true,
        routeToPermissionLoad: false,
        routeList: [],
        routeListLoading: true,
        routeListKeyword: '',
        routeListSelect: '',
        rolesList: [],
        rolesListLoading: true,
        rolesListKeyword: '',
        rolesListSelect: '',
        createRolesLoading: false,
        createRoleShow: false,
        createRoleModel: {
            roleName: '',
            roleGuardName: 'chat'
        }
    },
    computed: {
        CKLColumns () {
            let col = [
                {
                    title: 'ID',
                    key: 'id',
                    sortable: true
                },
                {
                    title: 'Token',
                    key: 'token'
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
            if (this.userId === 1) {
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
            if (this.userId === 1) {
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
            if (this.userId === 1) {
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
            if (this.userId === 1) {
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
        },
        exportData (type) {
            if (type === 1) {
                this.$refs.userListTable.exportCsv({
                    filename: 'UserList',
                    columns: this.userListColumns.filter((col, index) => index < this.userListColumns.length - 1),
                    data: this.userList.data.filter((col, index) => index < this.userListColumns.length - 1)
                });
            }
            if (type === 2) {
                this.$refs.groupListTable.exportCsv({
                    filename: 'GroupList',
                    columns: this.GLColumns.filter((col, index) => index < this.GLColumns.length - 1),
                    data: this.groupList.data.filter((col, index) => index < this.GLColumns.length - 1)
                });
            }
            if (type === 3) {
                this.$refs.clientKeyListTable.exportCsv({
                    filename: 'ClientKeyList',
                    columns: this.CKLColumns.filter((col, index) => index < this.CKLColumns.length - 1),
                    data: this.clientKeyList.data.filter((col, index) => index < this.CKLColumns.length - 1)
                });
            }
        },
        userListJump (page) {
            this.getUserList(page)
        },
        getUserList (page) {
            this.userListLoading = true
            http.get('/api/manage/chat/all/user?page=' + page + '&keyword=' + this.searchValue).then((r) => {
                this.userList = r.data.data
                this.userListLoading = false
            }).catch((e) => {
                console.log(e)
                this.userListLoading = false
            })
        },
        userListSearch (type) {
            if (type === 1 && this.searchValue) {
                this.getUserList(1)
            }
            if (type ===2 && this.searchValue) {
                this.searchValue = ''
                this.getUserList(1)
            }
        },
        getClientKeyList (page) {
            this.CKLLoading = true
            http.get('/api/manage/chat/key/list?page=' + page + '&keyword=' + this.searchValueCL).then((r) => {
                this.clientKeyList = r.data.data
                this.CKLLoading = false
            }).catch((e) => {
                console.log(e)
                this.CKLLoading = false
            })
        },
        clientKeyListJump (page) {
            this.getClientKeyList(page)
        },
        clientKeyListSearch (type) {
            if (type === 1 && this.searchValueCL) {
                this.getClientKeyList(1)
            }
            if (type ===2 && this.searchValueCL) {
                this.searchValueCL = ''
                this.getClientKeyList(1)
            }
        },
        getGroupList (page) {
            this.GLLoading = true
            http.get('/api/manage/chat/all/group?page=' + page + '&keyword=' + this.searchValueGL).then((r) => {
                this.groupList = r.data.data
                this.GLLoading = false
            }).catch((e) => {
                console.log(e)
                this.GLLoading = false
            })
        },
        groupListJump (page) {
            this.getGroupList(page)
        },
        groupListSearch (type) {
            if (type === 1 && this.searchValueGL) {
                this.getGroupList(1)
            }
            if (type ===2 && this.searchValueGL) {
                this.searchValueGL = ''
                this.getGroupList(1)
            }
        },
        showGroupInfo (groupId) {
            this.groupUserShow = true
            http.get('/api/chat/member/group/' + groupId).then((r) => {
                this.group = r.data.data
            })
        },
        getPermissionList (page) {
            this.perListLoading = true
            http.get('/api/manage/permission/list?page=' + page + '&keyword=' + this.permissionKeyword).then((r) => {
                this.permissionsList = r.data.data
                this.perListLoading = false
                this.routeToPermissionLoad =false
            }).catch((e) => {
                console.log(e)
                this.perListLoading = false
                this.routeToPermissionLoad = false
            })
        },
        permissionsListSearch (type) {
            if (type === 1 && this.permissionKeyword) {
                this.getPermissionList(1)
            }
            if (type ===2 && this.permissionKeyword) {
                this.permissionKeyword = ''
                this.getPermissionList(1)
            }
        },
        permissionsListJump (page) {
            this.getPermissionList(page)
        },
        getRouteList (page) {
            this.routeListLoading = true
            http.get('/api/manage/route/list?page=' + page + '&keyword=' + this.routeListKeyword + '&s=' + this.routeListSelect).then((r) => {
                this.routeList = r.data.data
                this.routeListLoading = false
            }).catch((e) => {
                console.log(e)
                this.routeListLoading = false
            })
        },
        routeListJump (page) {
            this.getRouteList(page)
        },
        routeListSearch (type) {
            if (type === 1 && this.routeListKeyword) {
                this.getRouteList(1)
            }
            if (type ===2 && this.routeListKeyword) {
                this.routeListKeyword = ''
                this.getRouteList(1)
            }
        },
        getRolesList (page) {
            this.rolesListLoading = true
            http.get('/api/manage/permission/role/list?page=' + page + '&keyword=' + this.rolesListKeyword).then((r) => {
                this.rolesList = r.data.data
                this.rolesListLoading = false
            }).catch((e) => {
                console.log(e)
                this.rolesListLoading = false
            })
        },
        rolesListJump (page) {
            this.getRolesList(page)
        },
        rolesListSearch (type) {
            if (type === 1 && this.rolesListKeyword) {
                this.getRolesList(1)
            }
            if (type ===2 && this.rolesListKeyword) {
                this.rolesListKeyword = ''
                this.getRolesList(1)
            }
        },
        routeToPermission () {
            this.perListLoading = true
            this.routeToPermissionLoad = true
            http.post('/api/manage/permission/route/set').then((r) => {
                this.getPermissionList(1)
                this.$Message.success({
                    content: 'success',
                    duration: 3
                });
            }).catch((e) => {
                console.log(e)
                this.perListLoading = false
                routeToPermissionLoad = false
            })
        },
        createRoles (name) {
            this.$refs[name].validate((valid) => {
                if (valid) {
                    this.createRolesLoading = true;

                } else {
                    this.$Notice.error({
                        title: '错误提醒',
                        desc: '填写格式有误'
                    })
                }
            })
        }
    },
    created () {
        this.getUserList(1)
    }
})
