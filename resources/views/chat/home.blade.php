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
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
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
</body>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript" src="http://vuejs.org/js/vue.min.js"></script>
<script type="text/javascript" src="http://unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qs/6.7.0/qs.min.js"></script>
<script>
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
                        axios.post('/auth/login', Qs.stringify({
                            email: this.formInline.mail,
                            password: this.formInline.password,
                            is_app: false
                        })).then((response) => {
                            console.log(response)
                            if (response.data.status_code === 200) {
                                location.href = 'chat/room'
                                this.loading = false
                            } else {
                                this.$Notice.error({
                                    title: '错误提醒',
                                    desc: response.data.message
                                })
                                this.loading = false
                            }
                        }).catch((e) => {
                            console.log(e)
                            this.loading = false
                            this.$Notice.error({
                                title: '错误提醒',
                                desc: '请求发生异常'
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
            },
            connectCallback (res) {
                if (res.type === 'error') {
                    this.loading = false;
                    this.$Notice.error({
                        title: '错误提醒',
                        desc: '验证失败'
                    })
                    ws.closeConnect()
                } else {
                    this.$router.push('/chat')
                }
            }
        }
    })
</script>
</html>
