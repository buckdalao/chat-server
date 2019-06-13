<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatApply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_apply', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('apply_user_id')->comment('申请用户');
            $table->unsignedInteger('friend_id')->comment('目标用户');
            $table->unsignedInteger('group_id')->comment('目标群');
            $table->string('remarks', 255)->default('')->comment('备注');
            $table->unsignedTinyInteger('apply_status')->comment('申请状态:0待审1通过2不通过');
            $table->unsignedInteger('apply_time')->comment('申请时间');
            $table->unsignedInteger('audit_time')->comment('审核时间');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_apply');
    }
}
