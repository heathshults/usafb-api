<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStageCoachRegEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('stg_coach_reg_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // columns
            $table->increments('id');
            $table->string('coach_id', 24);
            $table->string('registration_id', 24);
            $table->date('registration_date');
            $table->string('level');
            $table->string('level_type');
            $table->string('position');
            $table->string('organization_name')->nullable();
            $table->string('organization_state')->nullable();
            $table->string('league_name')->nullable();
            $table->integer('season_year');
            $table->string('season');
            $table->string('school_name')->nullable();
            $table->string('school_district')->nullable();
            $table->string('school_state')->nullable();
            $table->timestamps();
            
            $table->index('coach_id');
            $table->index('registration_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('stg_coach_reg_events');
    }
}
