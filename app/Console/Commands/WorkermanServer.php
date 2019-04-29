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
        $this->startGlobalData();
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
}
