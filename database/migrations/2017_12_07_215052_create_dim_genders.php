<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimGenders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_genders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // set autoinc primary key            
            $table->increments('id');            
            // columns
            $table->string('gender', 6);
            $table->string('gender_name', 6);
            // add index to gender column
            $table->index('gender');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_genders');        
    }
}
