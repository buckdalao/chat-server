<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GlobalData\Server;
use Illuminate\Console\Command;
use Workerman\Worker;
use App\Libs\Worker\Event;

class WorkermanServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'worker server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //因为workerman需要带参数 所以得强制修改
        global $argv;
        $action = $this->argument('action');
        if (!in_array($action, ['start', 'stop', 'restart', 'reload', 'connections', 'status'])) {
            $this->error('Error Arguments');
            exit;
        }
        $argv[0] = 'worker';
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d' : '';
        $this->start();
    }

    private function start()
    {
        //配置log目录
        Worker::$logFile = __DIR__ . '/../../../storage/logs/workerman.log';
        Worker::$pidFile = __DIR__ . '/../../../storage/logs/workerman.pid';
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        // $this->startGlobalData(); 关闭服务19.5.14
        $this->signalingServer();
        Worker::runAll();
    }

    private function startBusinessWorker()
    {
        $worker                  = new BusinessWorker();
        // business worker name
        $worker->name            = 'BusinessWorker';
        // business worker 进程数
        $worker->count           = getenv('BUSINESS_PROCESS_COUNT');
        // register服务注册地址
        $worker->registerAddress = getenv('REGISTER_SERVER');
        // worker 消息回调
        $worker->eventHandler    = Event::class;
    }

    private function startGateWay()
    {
        $sslCrt = getenv('SSL_LOCAL_CRT');
        $context = [];
        if ($sslCrt != 'null') {
            $context = array(
                'ssl' => array(
                    'local_cert'  => $sslCrt, // crt文件
                    'local_pk'    => getenv('SSL_LOCAL_KEY'),
                    'verify_peer' => false
                )
            );
        }
        $gateway = new Gateway("websocket://" . getenv('GATEWAY_SERVER'), $context);
        if ($sslCrt != 'null') {
            $gateway->transport = 'ssl';
        }
        // gateway名称，status方便查看
        $gateway->name                 = 'Gateway';
        // Gateway 进程数
        $gateway->count                = getenv('GATEWAY_PROCESS_COUNT');
        // 本机ip，分布式部署时使用内网ip
        $gateway->lanIp                = getenv('LAN_IP');
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort            = getenv('GATEWAY_START_PORT');
        // 心跳间隔
        $gateway->pingInterval         = 30;
        // 0 服务器向客户端发送心跳  1 反之
        $gateway->pingNotResponseLimit = 1;
        // 心跳数据 服务端发送
        $gateway->pingData             = '';
        // register服务地址
        $gateway->registerAddress      = getenv('REGISTER_SERVER');
    }

    private function startRegister()
    {
        new Register('text://' . getenv('REGISTER_SERVER'));
    }

    private function startGlobalData()
    {
        new Server(getenv('GLOBAL_SERVER'), getenv('GLOBAL_SERVER_PORT'));
    }

    private function signalingServer()
    {
        // 订阅主题和连接的对应关系
        $subject_connnection_map = array();
        $sslCrt = getenv('SSL_LOCAL_CRT');
        $context = [];
        if ($sslCrt != 'null') {
            $context = array(
                'ssl' => array(
                    'local_cert'  => $sslCrt, // crt文件
                    'local_pk'    => getenv('SSL_LOCAL_KEY'),
                    'verify_peer' => false
                )
            );
        }
        if ($sslCrt != 'null') {
            // websocket监听8877端口
            $worker = new Worker('websocket://0.0.0.0:8877', $context);
            $worker->transport = 'ssl';
        } else {
            // websocket监听8877端口
            $worker = new Worker('websocket://0.0.0.0:8877');
        }
        // 进程数只能设置为1，避免多个连接连连到不同进程
        // 不用担心性能问题，作为Signaling Server，workerman一个进程就足够了
        $worker->count = 1;
        // 进程名字
        $worker->name = 'Signaling Server';
        // 连接上来时设置个subjects属性，用来保存当前连接
        $worker->onConnect = function ($connection){
            $connection->subjects = array();
        };
        // 当客户端发来数据时
        $worker->onMessage = function($connection, $data)
        {
            $data = json_decode($data, true);
            switch ($data['cmd']) {
                // 订阅主题
                case 'subscribe':
                    $subject = $data['subject'];
                    subscribe($subject, $connection);
                    break;
                // 向某个主题发布消息
                case 'publish':
                    $subject = $data['subject'];
                    $event = $data['event'];
                    $data = $data['data'];
                    publish($subject, $event, $data, $connection);
                    break;
            }
        };
        // 客户端连接关闭时把连接从主题映射数组里删除
        $worker->onClose = function($connection){
            destry_connection($connection);
        };
        // 订阅
        function subscribe($subject, $connection) {
            global $subject_connnection_map;
            $connection->subjects[$subject] = $subject;
            $subject_connnection_map[$subject][$connection->id] = $connection;
        }
        // 取消订阅
        function unsubscribe($subject, $connection) {
            global $subject_connnection_map;
            unset($subject_connnection_map[$subject][$connection->id]);
        }
        // 向某个主题发布事件
        function publish($subject, $event, $data, $exclude) {
            global $subject_connnection_map;
            if (empty($subject_connnection_map[$subject])) {
                return;
            }
            foreach ($subject_connnection_map[$subject] as $connection) {
                if ($exclude == $connection) {
                    continue;
                }
                $connection->send(json_encode(array(
                    'cmd'   => 'publish',
                    'event' => $event,
                    'data'  => $data
                )));
            }
        }
        // 清理主题映射数组
        function destry_connection ($connection) {
            foreach ($connection->subjects as $subject) {
                unsubscribe($subject, $connection);
            }
        }
    }
}
