<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dim_levels', function (Blueprint $table) {
            // set autoinc primary key            
            $table->increments('id');
            // columns
            $table->string('level');
            $table->string('level_name');
            // add index to level column
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dim_levels');
    }
}
