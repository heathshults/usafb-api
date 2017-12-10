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
        Schema::create('dim_ages', function (Blueprint $table) {
            // set autoinc primary key
            $table->increments('id');
            // columns
            $table->string('range');
            $table->integer('range_min');
            $table->integer('range_max');
            $table->string('range_name');
            // add index to range column
            $table->index('range');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dim_ages');
    }
}
