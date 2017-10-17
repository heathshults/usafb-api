<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SourceRegistration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->unique();
            $table->string('api_key', 150)->unique();
            $table->timestamps();
        });
        DB::table('source')->insert(
            array(
                'name' => 'USFB',
                'api_key' => 'USFBKey'
            )
        );

        Schema::create('registration', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id')->unsigned();
            $table->string('type');
            $table->string('league', 255);
            $table->string('org_name', 255);
            $table->string('org_state', 80);
            $table->string('season', 20);
            $table->string('external_id')->nullable();
            $table->boolean('right_to_market')->nullable();
            $table->string('team_gender', 10)->nullable();
            $table->string('team_name', 100)->nullable();
            $table->string('school_district', 240)->nullable();
            $table->string('school_state', 80)->nullable();
            $table->string('first_name', 40);
            $table->string('middle_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('email');
            $table->string('gender', 10);
            $table->string('city', 40);
            $table->string('zip_code', 20);
            $table->dateTime('birth_date');
            $table->string('phone_number');
            $table->string('game_type');
            $table->string('level');
            $table->string('state');
            $table->string('address_first_line', 128);
            $table->string('address_second_line', 128)->nullable();
            $table->string('country', 100);
            $table->timestamps();

            $table->foreign('source_id')
                  ->references('id')
                  ->on('source')
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
        Schema::table('registration', function($table) {
            $table->dropColumn('source_id');
        });

        Schema::dropIfExists('source');
        Schema::dropIfExists('registration');
    }
}
