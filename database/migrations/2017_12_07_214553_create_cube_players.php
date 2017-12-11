<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCubePlayers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('cube_players', function (Blueprint $table) {
            // columns
            $table->integer('dim_date_id');
            $table->integer('dim_location_id');
            $table->integer('dim_age_id');
            $table->integer('dim_gender_id');
            $table->integer('dim_game_type_id');
            // remote fk to mongodb players collection record id
            $table->string('player_id', 24);
            // remote fk to mongodb players collection usafb id
            $table->integer('player_usafb_id');
            // set primary key
            $table->primary('dim_date_id');
            // add index on dimension fks
            $table->index('dim_location_id');
            $table->index('dim_age_id');
            $table->index('dim_gender_id');
            $table->index('dim_game_type_id');
            // add index on mongod coaches collection record id
            $table->index('player_usafb_id', 24);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('cube_players');
    }
}
