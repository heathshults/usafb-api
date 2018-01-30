<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStagePlayerEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql-dw')->create('stg_player_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // columns
            $table->increments('id');
            $table->string('player_id', 24);
            $table->string('id_usafb');
            $table->string('id_external')->nullable();
            $table->string('name_first');
            $table->string('name_middle')->nullable();
            $table->string('name_last');
            $table->date('dob');
            $table->string('gender');
            $table->string('zip');
            $table->integer('years_experience');
            $table->boolean('opt_in_marketing')->default(true);     
            $table->date('created_date');
            $table->date('updated_date')->nullable();
            $table->timestamps();
            
            // indicies
            $table->index('player_id');
            $table->index('gender');
            $table->index('zip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql-dw')->drop('stg_player_events');
    }
}
