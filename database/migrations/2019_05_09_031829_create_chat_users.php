<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id_1')->default(0)->comment('用户ID1');
            $table->unsignedInteger('user_id_2')->default(0)->comment('用户ID2');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at');
        });
        Schema::create('chat_users_message', function (Blueprint $table) {
            $table->increments('user_mes_id');
            $table->unsignedInteger('chat_id')->index()->comment('双人组聊天ID');
            $table->unsignedInteger('user_id')->default(0)->comment('消息发送者');
            $table->unsignedInteger('to_user_id')->default(0)->comment('消息接收者');
            $table->string('content',500)->default('')->comment('消息内容');
            $table->time('send_time')->default(0)->comment('消息发送时间');
            $table->unsignedTinyInteger('mes_type')->default(0)->comment('消息类型');
            $table->unsignedTinyInteger('status')->default(0)->comment('消息状态');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_users');
        Schema::dropIfExists('chat_users_message');
    }
}
