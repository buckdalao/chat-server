<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClientKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * example
     *     get key : php artisan client:key get --key=expire time (int)
     *     delete key : php artisan client:key del --key=client key (string)
     *     get key expire time: php artisan client:key ttl --key=client key (string)
     *
     * @var string
     */
    protected $signature = 'client:key {action} {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get client key';

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
        $action = $this->argument('action');
        $key = $this->options('key');
        if (!in_array($action, ['get', 'del', 'ttl']) || !isset($key['key'])) {
            $this->error('Error Arguments');
            exit;
        }
        switch ($action) {
            case 'get':
                if (is_numeric($key['key'])) {
                    $clientKey = app('App\Repositories\Tool\ClientAuthenticateRepository')->setToken($key['key']);
                    echo "\033[0;32mClient Key: $clientKey\033[0m" . PHP_EOL;
                } else {
                    $this->error('Param Error');
                }
                break;
            case 'del':
                if ($key['key']) {
                    app('App\Repositories\Tool\ClientAuthenticateRepository')->delToken($key['key']);
                    echo "\033[0;32mDelete Success\033[0m" . PHP_EOL;
                } else {
                    $this->error('Param Error');
                }
                break;
            case 'ttl':
                if ($key['key']) {
                    $time = app('App\Repositories\Tool\ClientAuthenticateRepository')->expToken($key['key']);
                    echo "\033[0;32mExpire Time: $time\033[0m" . PHP_EOL;
                } else {
                    $this->error('Param Error');
                }
                break;
        }
    }
}
