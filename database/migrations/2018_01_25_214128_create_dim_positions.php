<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_positions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // set autoinc primary key            
            $table->increments('id');
            // columns
            $table->string('position_type');
            $table->string('position');
            $table->string('position_name');
            $table->string('position_abbreviation');
            // add composite index (position_type/position)
            $table->index([ 'position_type', 'position' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_positions');
    }
}
