<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParentPlayerRegistrationRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parent_guardian', function (Blueprint $table) {
            $table->integer('player_registration_id')->unsigned();

            $table->foreign('player_registration_id')
                  ->references('id')
                  ->on('player_registration')
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
        Schema::table('parent_guardian', function($table) {
            $table->dropColumn('player_registration_id');
        });
    }
}
