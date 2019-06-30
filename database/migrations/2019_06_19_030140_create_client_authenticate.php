<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientAuthenticate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_authenticate', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('token')->unique()->comment('授权码');
            $table->unsignedInteger('expire_time')->comment('过期时间(时间戳)');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态');
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
        Schema::dropIfExists('client_authenticate');
    }
}
