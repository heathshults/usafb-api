<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Registrant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registrant', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type'); 
            $table->string('usafb_id', 32)->nullable(); 
            $table->string('first_name', 40);
            $table->string('middle_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('email');
            $table->string('gender', 10);
            $table->string('city', 40);
            $table->string('zip_code', 20);
            $table->dateTime('birth_date');
            $table->string('phone_number');
            $table->string('game_type');
            $table->string('level');
            $table->string('state');
            $table->string('address_first_line', 128);
            $table->string('address_second_line', 128)->nullable();
            $table->string('county', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registrant');
    }
}
