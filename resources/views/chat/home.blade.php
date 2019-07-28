@extends('chat.common.app')
@section('style')
    <style lang="less">
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
    </style>
@endsection
@section('content')
    <div id="id" class="m-ui-content">
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
                123123
            </i-content>
            <i-footer>
                <p style="text-align: center">Copyright © 2018-{{ date('Y') }} Mister Pan.</p>
            </i-footer>
        </Layout>
    </div>
@endsection
@section('script')
    <script>
        new Vue({
            el: '#id',
            data: {

            },
            methods: {
                setting (name) {
                    if (name === 'logout') {
                        location.href = '/auth/logout'
                        console.log(name)
                    }
                }
            }
        })
    </script>
@endsection