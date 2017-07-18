<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlayerParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('player_registration')) {
            Schema::create('player_parent', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('player_registration_id')->unsigned();
                $table->foreign('player_registration_id')->references('id')->on('player_registration');
                $table->string('first_name', 128);
                $table->string('last_name', 128);
                $table->string('email');
                $table->string('cell_phone');
                $table->string('home_phone');
                $table->string('work_phone'); // Why is this field in both tables?
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_parent');
    }
}
