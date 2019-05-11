<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Chat Server</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="http://unpkg.com/iview/dist/styles/iview.css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .layout{
            border: 1px solid #d7dde4;
            background: #f5f7f9;
            position: relative;
            border-radius: 4px;
            overflow: hidden;
            width: 1200px;
            max-height: 800px;
        }
        .layout-header-bar{
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.1);
        }
        .menu-item span{
            display: inline-block;
            overflow: hidden;
            width: 69px;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: bottom;
            transition: width .2s ease .2s;
        }
        .menu-item i{
            transform: translateX(0px);
            transition: font-size .2s ease, transform .2s ease;
            vertical-align: middle;
            font-size: 16px;
        }
        .collapsed-menu span{
            width: 0px;
            transition: width .2s ease;
        }
        .collapsed-menu i{
            transform: translateX(5px);
            transition: font-size .2s ease .2s, transform .2s ease .2s;
            vertical-align: middle;
            font-size: 22px;
        }
    </style>
    <style type="text/less">
        .m-message {
            padding: 10px 15px;
            overflow-y: scroll;
            height: ~'calc(100% - 190px)';

        li {
            margin-bottom: 15px;
        }
        .time {
            margin: 7px 0;
            text-align: center;

        > span {
              display: inline-block;
              padding: 0 18px;
              font-size: 12px;
              color: #fff;
              border-radius: 2px;
              background-color: #dcdcdc;
          }
        }
        .avatar {
            float: left;
            margin: 0 10px 0 0;
            border-radius: 3px;
        }
        .text {
            display: inline-block;
            position: relative;
            padding: 0 10px;
            max-width: ~'calc(100% - 40px)';
            min-height: 30px;
            line-height: 2.5;
            font-size: 12px;
            text-align: left;
            word-break: break-all;
            background-color: #fafafa;
            border-radius: 4px;

        &:before {
             content: " ";
             position: absolute;
             top: 9px;
             right: 100%;
             border: 6px solid transparent;
             border-right-color: #fafafa;
         }
        img {
            vertical-align: middle
        }
        }

        .self {
            text-align: right;

        .avatar {
            float: right;
            margin: 0 0 0 10px;
        }
        .text {
            background-color: #b2e281;

        &:before {
             right: inherit;
             left: 100%;
             border-right-color: transparent;
             border-left-color: #b2e281;
         }
        }
        }
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div id="app" class="layout">
            <Layout>
                <Sider breakpoint="md" collapsible :collapsed-width="78" v-model="isCollapsed">
                    <i-menu active-name="1-2" theme="dark" width="auto" :class="menuitemClasses">
                        <menu-item name="1-1">
                            <Icon type="ios-navigate"></Icon>
                            <span>{{ $users['name'] }}</span>
                        </menu-item>
                        <menu-item name="1-2">
                            <Icon type="ios-search"></Icon>
                            <span>Option 2</span>
                        </menu-item>
                        <menu-item name="1-3">
                            <Icon type="ios-settings"></Icon>
                            <span>Option 3</span>
                        </menu-item>
                    </i-menu>
                    <div slot="trigger"></div>
                </Sider>
                <Layout>
                    <Content :style="{margin: '20px', background: '#fff', minHeight: '220px'}">
                        <div class="m-message" v-scroll-bottom="session.messages">
                            <ul>
                                <li v-for="item in session.messages">
                                    <p v-if="item.showTime" class="time"><span>@{{item.date | time}}</span></p>
                                    <div class="main" :class="{ self: item.self }">
                                        <img class="avatar" width="30" height="30" :src="avatar(item)"/>
                                        <div class="text" v-html="html(item.text)"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </Content>
                </Layout>
            </Layout>
        </div>
    </div>
</div>
</body>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript" src="http://vuejs.org/js/vue.min.js"></script>
<script type="text/javascript" src="http://unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qs/6.7.0/qs.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            isCollapsed: false,
            session: {
                userId: parseInt('{{ $users['id'] }}'),
                messages: [
                    {self: true, text: 'hello', showTime: true, date: '2019-05-08 16:53:02'},
                    {self: false, text: 'hello', showTime: false, date: '2019-05-08 16:53:02'},
                    {self: true, text: 'hello', showTime: true, date: '2019-05-08 16:53:02'}
                ]
            },
            userList: [
                {userId: 3, img: 'resources/emoji/xxx.png', name: 'pw'}
            ],
            users: {
                userId: parseInt('{{ $users['id'] }}'),
                img: 'a/a.png',
                name: 'pw'
            }
        },
        computed: {
            menuitemClasses: function () {
                return [
                    'menu-item',
                    this.isCollapsed ? 'collapsed-menu' : ''
                ]
            },
            sessionUser () {
                let users = this.userList.filter(item => item.userId === this.session.userId)
                return users[0]
            }
        },
        methods: {
            // 筛选出用户头像
            avatar (item) {
                // 如果是自己发的消息显示登录用户的头像
                let user = item.self ? this.users : this.sessionUser
                return user && user.img
            },
            html (str) {
                return str.replace(/\\/g, '')
            }
        },
        filters: {
            // 将日期过滤为 hour:minutes
            time (date) {
                let d = date
                if (typeof date === 'string') {
                    date = new Date(date)
                }
                if (date.getDay() !== new Date().getDay()) {
                    return d
                }
                return date.getHours() + ':' + date.getMinutes()
            }
        },
        directives: {
            // 发送消息后滚动到底部
            'scroll-bottom' (el) {
                Vue.nextTick(() => {
                    el.scrollTop = el.scrollHeight - el.clientHeight
                })
            }
        }
    })
</script>
</html>

