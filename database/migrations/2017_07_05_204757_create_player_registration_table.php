<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('player') && Schema::hasTable('player_level') && Schema::hasTable('game_type')) {
            Schema::create('player_registration', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sport_years');
                $table->string('address_first_line', 128);
                $table->boolean('birth_certificate');
                $table->string('phone', 30);
                $table->string('city', 128);
                $table->string('country', 128);
                $table->string('grade'); 
                $table->dateTime('birth_date');
                $table->string('email', 254);
                $table->string('first_name', 32); 
                $table->integer('game_type_id')->unsigned();
                $table->foreign('game_type_id')->references('id')->on('game_type');
                $table->string('gender', 6);
                $table->string('height');
                $table->integer('graduation_year');
                $table->string('instagram_handle')->nullable();;
                $table->string('last_name', 32);
                $table->string('league', 64);
                $table->integer('level_id')->unsigned();
                $table->foreign('level_id')->references('id')->on('player_level');
                $table->string('middle_name', 64);
                $table->string('org_name');
                $table->string('org_state');
                $table->text('sports');
                $table->string('photo');
                $table->text('positions');
                $table->string('school');
                $table->string('school_district');
                $table->string('school_state');
                $table->string('season');
                $table->string('state', 128);
                $table->string('team_name');
                $table->integer('team_age_group');
                $table->string('team_gender');
                $table->string('twitter_handle')->nullable();;
                $table->boolean('usafb_market');
                $table->string('weight');
                $table->integer('player_id')->unsigned();
                $table->foreign('player_id')->references('id')->on('player');
                $table->string('zip_code', 10);
                
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
        Schema::dropIfExists('player_registration');
    }
}
