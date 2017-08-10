<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlayerRegistration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('positions', 32)->nullable(); 
            $table->string('team_age_group', 4)->nullable();
            $table->string('school_name', 255)->nullable();
            $table->string('grade', 4);
            $table->string('height', 20);
            $table->string('graduation_year', 4);
            $table->string('instagram', 30)->nullable();
            $table->text('sports');
            $table->string('twitter', 15)->nullable();
            $table->string('weight', 15);
            $table->integer('years_at_sport');
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
        Schema::dropIfExists('player_registration');
    }
}
