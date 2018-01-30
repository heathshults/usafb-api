<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimAges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_ages', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // set autoinc primary key
            $table->increments('id');
            // columns
            $table->integer('age');
            $table->string('age_group');
            // add index to range column
            $table->index('age');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_ages');
    }
}
