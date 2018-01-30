<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimPlayers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('dim_players', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // columns
            $table->increments('id');
            $table->string('player_id', 24);
            $table->string('id_usafb');
            $table->string('id_external');            
            $table->string('name_first');
            $table->string('name_middle');
            $table->string('name_last');
            $table->date('dob');
            $table->integer('years_experience');
            $table->integer('dim_location_id')->unsigned();
            $table->integer('dim_gender_id')->unsigned();
            $table->timestamps();                 

            $table->foreign('dim_location_id')
                ->references('id')->on('dim_locations');
            $table->foreign('dim_gender_id')
                ->references('id')->on('dim_genders');

            $table->index('player_id');            
            $table->index('years_experience');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('dim_players');
    }
}
