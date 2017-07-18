<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('player')) {
            Schema::create('player_team', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('player_id')->unsigned();
                $table->foreign('player_id')->references('id')->on('player');
                $table->string('team_name', 128);
                $table->string('school', 128);
                $table->string('school_state');
                $table->integer('team_age_group');
                $table->string('team_gender', 6); // Why is this field in both tables?
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
        Schema::dropIfExists('player_team');
    }
}
