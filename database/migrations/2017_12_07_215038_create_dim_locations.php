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
        Schema::create('dim_locations', function (Blueprint $table) {
            // set autoinc primary key
            $table->increments('id');
            // columns
            $table->string('city', 50);
            $table->char('state', 2);            
            $table->string('zip', 5);
            $table->string('zip_plus_5', 10);
            $table->double('zip_centroid_lat', 11, 8);
            $table->double('zip_centroid_lng', 11, 8);
            // add index on zip and state columns
            $table->index('zip');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dim_locations');
    }
}
