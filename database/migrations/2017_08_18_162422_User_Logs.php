<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users_logs',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_id');
                $table->enum('event_type', ['CREATE', 'UPDATE']);
                $table->string('data_field')->nullable();
                $table->string('old_value')->nullable();
                $table->string('new_value')->nullable();
                $table->string('created_by');
                $table->string('created_by_id');
                $table->dateTime('created_at');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_logs');
    }
}
