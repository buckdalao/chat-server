<?php

namespace App\Console\Commands;

use App\Libs\Traits\WsMessageTrait;
use App\Models\Chat\ChatGroupMessage;
use App\Repositories\Chat\ChatGroupMessageRepository;
use App\Repositories\Chat\ChatUsersMessageRepository;
use App\Repositories\Chat\ChatUsersRepository;
use Illuminate\Console\Command;

class ChatMessage extends Command
{
    use WsMessageTrait;
    /**
     * The name and signature of the console command.
     * action  [
     *         save --- Temporary message overflow saved to database
     *         list --- View all messages to save records and the number of saved messages
     *         ttl --- Redis command ttl
     *         ]
     * key  =>  redis key
     * example php artisan message save | list | ttl --key=redis key
     *
     * @var string
     */
    protected $signature = 'message {action} {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'save expire chat messages.';

    /**
     * @var ChatGroupMessage
     */
    protected $chatCroupMessModel;

    protected $chatUserMessRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChatGroupMessage $groupMessage, ChatUsersMessageRepository $chatUsersMessageRepository)
    {
        parent::__construct();
        $this->chatCroupMessModel = $groupMessage;
        $this->chatUserMessRepository = $chatUsersMessageRepository;
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
        if (!in_array($action, ['save', 'list', 'ttl'])) {
            $this->error('Error Arguments');
            exit;
        }
        switch ($action) {
            case 'save':
                $this->saveAllQueueData([$this->chatCroupMessModel, 'saveExpire'], [$this->chatUserMessRepository, 'saveExpire']);
                echo "\033[0;32mSave successfully\033[0m" . PHP_EOL;
                break;
            case 'list':
                $list = $this->getKeySaveCount();
                if (sizeof($list)) {
                    foreach ($list as $v) {
                        echo "\033[0;32mKey: " . $v['key'] . '  Count: ' . $v['count'] . "\033[0m" . PHP_EOL;
                    }
                } else {
                    $this->error('No data');
                }
                break;
            case 'ttl':
                $t = $this->ttl($key['key']);
                echo "\033[0;32mExpire: " . $t . "\033[0m" . PHP_EOL;
                break;
        }
    }
}
