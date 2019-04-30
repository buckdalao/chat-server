##项目简介
 * 使用Laravel框架结合Workerman搭建的简易消息服务
 
 package|version 
 ---|---
 PHP|>7.1.3
 Laravel|5.7
 Workerman|3.5
 
 ##使用说明
 * workerman 相关配置
 ```
 REGISTER_SERVER=0.0.0.0:1239
 GATEWAY_SERVER=0.0.0.0:9526
 SSL_LOCAL_CRT=null #ssl证书文件路径
 SSL_LOCAL_KEY=null
 LAN_IP=0.0.0.0
 GATEWAY_START_PORT=2900
 GATEWAY_PROCESS_COUNT=4
 BUSINESS_PROCESS_COUNT=4
 GLOBAL_SERVER=0.0.0.0
 GLOBAL_SERVER_PORT=2207
 ```
 * 引入包
 
 ```
 composer install
 ```
 
 * 启动workerman服务
 
```
php artisan worker {start|stop|restart|reload|status} {--d}
```
 

## License

The Laravel framework is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
