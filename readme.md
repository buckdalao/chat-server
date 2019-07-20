## 项目简介
 * 使用Laravel框架结合Workerman搭建的简易消息服务

package|version 
:---:|:---:
PHP|>7.1.3
Laravel|5.7
Workerman|3.5
 
## 使用说明
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
 
 cp .env.example .env
 
 php artisan key:generate
 
 php artisan jwt:secret
  
 php artisan migrate
 
 php artisan db:seed
 
php artisan storage:link  #建立public/storage软链接，homestead需要以管理员身份启动
 ```
 
 * 启动workerman服务
 
```
php artisan worker {start|stop|restart|reload|status} {--d}
```
* 开启任务调度
```$xslt
   #在crontab 中添加任务
   
   * * * * * /your_php_install_path/bin/php /your_laravel_project_path/artisan schedule:run >> /dev/null 2>&1
```

* artisan快捷命令
```$xslt
# redis消息处理与查询

php artisan message {save|list|ttl} {--key=}
     * action  [
     *         save --- Temporary message overflow saved to database
     *         list --- View all messages to save records and the number of saved messages
     *         ttl --- Redis command ttl
     *         ]
     * key  =>  redis key
     * example php artisan message save | list | ttl --key=redis key
     
     
# 创建repository文件   
# 生成文件的namespace 是 App\Repositories\   
# model是基于 App\Models\下  
# model可选参数

php artisan make:repository exampleRepository --model=User // 该文件namespace App\Repositories\exampleRepository  use App\Models\User  生成文件在app/Repositories目录

php artisan make:repository Test/exampleRepository --model=Test/User // 该文件namespace App\Repositories\Test\exampleRepository  use App\Models\Test\User  生成文件在app/Repositories/Test目录

# 获取client key

get key : php artisan client:key get --key=expire_time (int) // expire_time 单位seconds  将返回的key值加入到.env CLIENT_KEY
delete key : php artisan client:key del --key=client_key (string) // 删除client key 删除后立即失效
get key expire time: php artisan client:key ttl --key=client_key (string) // -1 永久有效 -2 未知key 0 已过期  返回剩余时间
```
 

## License

The Laravel framework is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
