<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimLevelTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_level_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // set autoinc primary key            
            $table->increments('id');            
            // columns
            $table->string('level_type');
            $table->string('level_type_name');
            // add index on gender
            $table->index('level_type');         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_level_types');
    }
}
