<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_group', function (Blueprint $table) {
            $table->increments('group_id');
            $table->string('group_name', 255)->default('')->comment('群名称');
            $table->unsignedTinyInteger('group_status')->default(0)->comment('群状态');
            $table->unsignedInteger('user_id')->default(0)->comment('群主');
            $table->string('photo', 100)->comment('群头像');
            $table->unsignedInteger('group_number')->comment('群号')->unique();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at');
        });
        Schema::create('chat_group_message', function (Blueprint $table) {
            $table->increments('group_mes_id');
            $table->unsignedInteger('group_id')->index()->comment('群ID');
            $table->unsignedInteger('user_id')->default(0)->comment('消息发送者');
            $table->string('content',500)->default('')->comment('消息内容');
            $table->unsignedInteger('send_time')->default(0)->comment('消息发送时间');
            $table->unsignedTinyInteger('mes_type')->default(0)->comment('消息类型');
            $table->unsignedTinyInteger('status')->default(0)->comment('消息状态');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at');
        });
        Schema::create('chat_group_users', function (Blueprint $table) {
            $table->increments('group_user_id');
            $table->unsignedInteger('group_id')->index()->comment('群ID');
            $table->unsignedInteger('user_id')->index()->comment('用户ID');
            $table->string('group_user_name',255)->default('')->comment('群昵称');
            $table->unsignedTinyInteger('status')->default(0)->comment('成员状态');
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
        Schema::dropIfExists('chat_group');
        Schema::dropIfExists('chat_group_message');
        Schema::dropIfExists('chat_group_users');
    }
}
