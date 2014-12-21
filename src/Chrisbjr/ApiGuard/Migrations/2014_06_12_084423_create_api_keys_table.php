<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiKeysTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('key', 40);
            $table->smallInteger('level');
            $table->boolean('ignore_limits');
            $table->nullableTimestamps();
            $table->softDeletes();
            $table->unique('key');
        });

        Schema::create('api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_key_id');
            $table->string('route', 50);
            $table->string('method', 6);
            $table->text('params');
            $table->string('ip_address');
            $table->nullableTimestamps();
            $table->index('api_key_id');
            $table->index('route');
            $table->index('method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('api_keys');
        Schema::drop('api_logs');
    }

}
