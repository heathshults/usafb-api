<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStageCoachEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('stg_coach_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // columns
            $table->increments('id');
            $table->string('coach_id', 24);
            $table->string('id_usafb');
            $table->string('id_external')->nullable();
            $table->string('name_first');
            $table->string('name_middle')->nullable();
            $table->string('name_last');
            $table->string('email');
            $table->integer('years_experience');
            $table->boolean('opt_in_marketing')->default(true);            
            $table->date('dob');
            $table->string('zip', 5);
            $table->string('gender');
            $table->date('created_date');
            $table->date('updated_date')->nullable();  
            $table->timestamps();
                
            // indicies
            $table->index('coach_id');
            $table->index('zip');
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
        Schema::connection('mysql-dw')->drop('stringg_coach_events');
    }
}
