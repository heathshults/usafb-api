<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimGameTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dim_game_types', function (Blueprint $table) {
            // set autoinc primary key            
            $table->increments('id');            
            // columns
            $table->string('game_type');
            $table->string('game_type_name');
            // add index on gender
            $table->index('game_type');         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dim_game_types');
    }
}
