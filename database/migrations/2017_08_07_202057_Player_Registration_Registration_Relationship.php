<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlayerRegistrationRegistrationRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_registration', function (Blueprint $table) {
            $table->integer('registration_id')->unsigned();

            $table->foreign('registration_id')
                  ->references('id')
                  ->on('registration')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_registration', function($table) {
            $table->dropColumn('registration_id');
        });
    }
}
