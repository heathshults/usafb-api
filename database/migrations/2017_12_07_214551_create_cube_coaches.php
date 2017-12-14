<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCubeCoaches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('cube_coaches', function (Blueprint $table) {
            // columns
            $table->integer('dim_date_id');
            $table->integer('dim_location_id');
            $table->integer('dim_game_type_id');
            $table->integer('dim_gender_id');
            // remote fk to mongodb coaches collection record id
            $table->string('coach_id', 24);
            // remote fk to mongodb coaches collection usafb id
            $table->integer('coach_usafb_id');
            // set primary key
            $table->primary('dim_date_id');
            // add index on dimension fks
            $table->index('dim_location_id');
            $table->index('dim_game_type_id');
            $table->index('dim_gender_id');
            // add index on mongod coaches collection record id
            $table->index('coach_id', 24);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('cube_coaches');        
    }
}
