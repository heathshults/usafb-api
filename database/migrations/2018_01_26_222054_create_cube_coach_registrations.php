<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCubeCoachRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('cube_coach_registrations', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // columns
            $table->string('registration_id', 24);
            $table->integer('dim_date_id');
            $table->integer('dim_coach_id')->unsigned();
            $table->integer('dim_level_id')->unsigned();
            $table->integer('dim_level_type_id')->unsigned();
            $table->integer('dim_position_id')->unsigned();
            $table->string('organization_name', 50);
            $table->string('organization_state', 2);
            $table->string('league_name', 50);            
            $table->integer('season_year');
            $table->string('season', 10);
            $table->string('school_name', 50);
            $table->string('school_district', 50);
            $table->string('school_state', 2);     
            $table->timestamps();
            
            $table->primary('registration_id');
            
            // add fks and indicies
            $table->foreign('dim_date_id')
                  ->references('id')->on('dim_dates');
            $table->foreign('dim_level_id')
                  ->references('id')->on('dim_levels');
            $table->foreign('dim_level_type_id')
                  ->references('id')->on('dim_level_types');
            $table->foreign('dim_position_id')
                  ->references('id')->on('dim_positions');
            $table->foreign('dim_coach_id')
                  ->references('id')->on('dim_coaches');
            
            $table->index('season');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('cube_coach_registrations');
    }
}
