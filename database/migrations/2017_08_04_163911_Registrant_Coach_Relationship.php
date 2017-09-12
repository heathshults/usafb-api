<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RegistrantCoachRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coach', function (Blueprint $table) {
            $table->integer('registrant_id')->unsigned();

            $table->foreign('registrant_id')
                  ->references('id')
                  ->on('registrant')
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
        Schema::table('coach', function($table) {
            $table->dropColumn('registrant_id');
        });
    }
}
