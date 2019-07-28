@extends('chat.common.app')
@section('style')
    <style>
        .full-height {
            height: 100vh;
            position: relative;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection
@section('content')
    <div class="flex-center full-height">
        <div class="content">
            <div id="app">
                <Card :bordered="false" :xs="8" :sm="4" :md="6" :lg="8">
                    <p slot="title">登录</p>
                    <div class="card-body">
                        <i-form ref="formInline" :model="formInline" :rules="ruleInline">
                            <form-item prop="mail">
                                <i-input v-model="formInline.mail" placeholder="请输入邮箱" size="large">
                                    <Icon type="md-mail" slot="prefix" style="font-size: 18px;"></Icon>
                                </i-input>
                            </form-item>
                            <form-item prop="password">
                                <i-input v-bind:type="passwordType" v-model="formInline.password" placeholder="请输入密码"
                                         size="large">
                                    <Icon type="md-lock" slot="prefix" style="font-size: 18px;"></Icon>
                                    <Icon v-if="!formInline.is_eye" type="ios-eye-off-outline" slot="suffix"
                                          style="font-size: 18px;cursor: pointer;" @click="funcShow"></Icon>
                                    <Icon v-else type="ios-eye-outline" slot="suffix" style="font-size: 18px;cursor: pointer;"
                                          @click="funcShow"></Icon>
                                </i-input>
                            </form-item>
                            <form-item>
                                <i-button type="primary" :loading="loading" @click="handleSubmit('formInline')" long>
                                    <span v-if="!loading">Sign in</span>
                                    <span v-else>Loading...</span>
                                </i-button>
                            </form-item>
                        </i-form>
                    </div>
                </Card>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    var config = {
        timeout: 1000 * 60,
        headers: {
            'Accept': 'application/prs.chat.v1+json'
        }
    }
    new Vue({
        el: '#app',
        data: {
            formInline: {
                password: '',
                mail: '',
                is_eye: false
            },
            loading: false,
            passwordType: 'password'
        },
        computed: {
            ruleInline () {
                return {
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                        {type: 'string', min: 6, message: '密码长度最小为6位', trigger: 'blur'}
                    ],
                    mail: [
                        {required: true, message: '请输入邮箱', trigger: 'blur'},
                        {type: 'email', message: '邮箱格式错误', trigger: 'blur'}
                    ]
                }
            }
        },
        methods: {
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        this.loading = true;
                        config.headers = {
                            'Accept': 'application/prs.chat.v1+json',
                        }
                        axios.post('/auth/login', Qs.stringify({
                            email: this.formInline.mail,
                            password: this.formInline.password,
                            is_app: false
                        }), config).then((response) => {
                            console.log(response)
                            location.href = '/chat'
                        }).catch((e) => {
                            console.log(e)
                            this.loading = false
                            this.$Notice.error({
                                title: '错误提醒',
                                desc: e.response.data.data
                            })
                        })
                    } else {
                        this.$Notice.error({
                            title: '错误提醒',
                            desc: '填写格式有误'
                        })
                    }
                })
            },
            // 密码显示隐藏
            funcShow: function () {
                if (this.formInline.is_eye === false) {
                    this.formInline.is_eye = true
                    this.passwordType = 'text'
                } else {
                    this.formInline.is_eye = false
                    this.passwordType = 'password'
                }
            }
        }
    })
</script>
@endsection
