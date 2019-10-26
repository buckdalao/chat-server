@extends('chat.common.app')
@section('style')
    <style>
        .m-ui-content {
            width: 100%;
            height: 100%;
        }
        .m-ui-head-right {
            float: right;
            height: 100%;
            text-align: center;
        }
        .m-ui-head-right > div {
            padding: 0 10px;
        }
        .m-ui-head-left {
            float: left;
        }
        .m-ui-header {
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.1);
        }
        .menu-icon{
            transition: all .3s;
        }
        .rotate-icon{
            transform: rotate(-90deg);
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
            margin: 0;
        }
        .tab-content {
            min-height: calc(100vh - 133px);
            padding: 16px;
        }
        .layout-content {
            transition:margin 0.3s;
            -moz-transition:margin 0.3s; /* Firefox 4 */
            -webkit-transition:margin 0.3s; /* Safari and Chrome */
            -o-transition:margin 0.3s; /* Opera */
        }
    </style>
    @yield('css')
@endsection
@section('content')
    <div id="id" class="m-ui-content">
        <input type="hidden" value="{{ $user->id }}" id="user_id">
        <Layout style="height: 100%">
            <Sider ref="side" class="m-ui-side" hide-trigger collapsible :collapsed-width="78" v-model="isCollapsed" :style="{position: 'fixed', height: '100vh', left: 0, overflow: 'auto'}">
                <i-menu :active-name="pageName" @on-select="selectMenu" theme="dark" width="auto" :class="menuitemClasses">
                    <menu-item name="chat">
                        <Icon type="ios-navigate"></Icon>
                        <span>主面板</span>
                    </menu-item>
                    <menu-item name="system">
                        <Icon type="ios-settings"></Icon>
                        <span>系统设置</span>
                    </menu-item>
                </i-menu>
            </Sider>
            <Layout :style="{marginLeft: sideWidth + 'px'}" class="layout-content">
                <i-header class="m-ui-header">
                    <div class="m-ui-head-left">
                        <Icon @click.native="collapsedSider" :class="rotateIcon" type="md-menu" size="24" :style="{'cursor': 'pointer'}"></Icon>
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
                <i-content class="tab-content">
                    @yield('body_cont')
                </i-content>
                <i-footer>
                    <p style="text-align: center">Copyright © 2018-{{ date('Y') }} Mister Pan.</p>
                </i-footer>
            </Layout>
        </Layout>
        <back-top></back-top>
    </div>
@endsection
