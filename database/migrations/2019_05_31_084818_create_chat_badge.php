<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatBadge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_group_message_badge', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->index()->comment('群ID');
            $table->unsignedInteger('user_id')->default(0)->comment('提醒用户');
            $table->unsignedInteger('count')->default(0)->comment('消息总数');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at');
        });
        Schema::create('chat_message_badge', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_id')->index()->comment('双人组聊天ID');
            $table->unsignedInteger('user_id')->default(0)->comment('提醒用户');
            $table->unsignedInteger('count')->default(0)->comment('消息总数');
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
        Schema::dropIfExists('chat_group_message_badge');
        Schema::dropIfExists('chat_message_badge');
    }
}
