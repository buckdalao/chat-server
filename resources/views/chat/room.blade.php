<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

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

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
                @endauth
        </div>
    @endif

    <div class="content">
        <div class="title m-b-md">
            Laravel
        </div>

        <div class="links">
            <a href="https://laravel.com/docs">Docs</a>
            <a href="https://laracasts.com">Laracasts</a>
            <a href="https://laravel-news.com">News</a>
            <a href="https://blog.laravel.com">Blog</a>
            <a href="https://nova.laravel.com">Nova</a>
            <a href="https://forge.laravel.com">Forge</a>
            <a href="https://github.com/laravel/laravel">GitHub</a>
        </div>
    </div>
    <input type="text" id="token" placeholder="token"><input type="text" id="key" placeholder="key"><button onclick="connect()">连接</button><br>
    <input type="text" id="msg"><button onclick="sendMsg()">发送</button>
</div>
</body>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript" src="http://vuejs.org/js/vue.min.js"></script>
<script type="text/javascript" src="http://unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qs/6.7.0/qs.min.js"></script>
</html>
<script>
    var socket;
    var token;
    var timer;
    var client_id;
    function connect(){
        socket = new WebSocket('ws://reconsitutionfs.com:9526')
        socket.onopen = onopensocket
        socket.onmessage = onmessage
        socket.onerror = socketError
        socket.onclose = socketClose
        timer = setInterval(function(){
            socket.send('{"type":"ping"}')
        },1000*25)
        var token = "bearer " + document.getElementById('token').value
        var key = document.getElementById('key').value
        var config = {
            timeout: 1000 * 60,
            headers: {
                'Accept': 'application/prs.chat.v1+json',
                'Authorization': token,
                'Custom-Token': key
            }
        }
        setTimeout(() => {
            axios.post('/api/chat/init', Qs.stringify({
                connect_id: client_id
            }),config)
        }, 3000)
    }
    function onopensocket () {
        console.log('连接服务器成功')
    }
    function onmessage (mes) {
        console.log(mes)
        if (mes.data.length === 0 || mes.data === '') {
            return false
        }
        data = evil(mes.data);
        if (data.type == 3){
            client_id = data.data
        }
        if (data.type == 'error') {
            socket.close();
            clearInterval(timer);
            timer = null;
        }
    }
    var evil = function (fn) {
        // 一个变量指向Function，防止有些前端编译工具报错
        let Fn = Function
        return new Fn('return ' + fn)()
    }
    function socketError () {
        console.log('服务器连接出错，定时重连......')
    }
    function socketClose () {
        console.log('服务器连接已断开，定时重连......')
    }
    function sendMsg () {
        var token = "bearer " + document.getElementById('token').value
        var key = document.getElementById('key').value
        var config = {
            timeout: 1000 * 60,
            headers: {
                'Accept': 'application/prs.chat.v1+json',
                'Authorization': token,
                'Custom-Token': key
            }
        }
        axios.post('/api/chat/chatMessage', Qs.stringify({
            chat_id: 1,
            content: document.getElementById('msg').value
        }), config);
        /*socket.send(JSON.stringify({
            type: 'message',
            content: document.getElementById('msg').value,
            group_id: 0,
            send_to_uid: 4,
            chat_id: 1,
            uid: 1,
            user_name: 'buck',
            photo: 'http://reconsitutionfs.com/storage/photos/photo.jpg'
        }))*/
    }
</script>
