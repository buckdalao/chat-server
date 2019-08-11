<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/iview/dist/styles/iview.css">

    <!-- Styles -->
    @yield('style')
</head>
<body>
@yield('content')
</body>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    var csrf_token = document.querySelector('meta[name=csrf-token]').content;
</script>
<script type="text/javascript" src="https://unpkg.com/vue@2.6.10/dist/vue.js"></script>
<script type="text/javascript" src="https://unpkg.com/iview/dist/iview.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/crypto-js@3.1.9-1/crypto-js.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qs/6.7.0/qs.min.js"></script>
<script src="/js/chat/config.js?v={{rand(1,99)}}"></script>
<script src="/js/chat/axios.js?v={{rand(1,99)}}"></script>
@yield('script')
</html>
