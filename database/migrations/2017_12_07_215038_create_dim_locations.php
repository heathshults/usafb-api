<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_locations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // set autoinc primary key
            $table->increments('id');
            // columns
            $table->string('zip', 5);
            $table->string('city', 50);
            $table->string('county', 50);
            $table->string('county_fips', 25);
            $table->char('state', 2);            
            $table->string('area_code',3);
            $table->double('latitude', 11, 8);
            $table->double('longitude', 11, 8);
            // add index on zip and state columns
            $table->index('zip');
            $table->index('state');
            $table->index('county_fips');
            $table->index('area_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_locations');
    }
}
