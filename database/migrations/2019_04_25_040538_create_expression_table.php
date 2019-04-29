<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpressionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expression', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phrase');
            $table->tinyInteger('type');
            $table->string('common');
            $table->string('path', 250);
            $table->tinyInteger('is_delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expression');
    }
}
