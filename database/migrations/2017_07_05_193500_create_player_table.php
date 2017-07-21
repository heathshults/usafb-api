<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player', function (Blueprint $table) {
            $table->increments('id');
            $table->string('usadfb_id', 32)->nullable(); 
            $table->string('salesforce_id', 32)->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('middle_name', 40)->nullable();
            $table->string('last_name', 80)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('gender', 6)->nullable();
            $table->string('city', 40)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->integer('sport_years')->nullable();
            $table->string('graduation_year', 4)->nullable();
            $table->string('grade')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->boolean('usafb_market')->nullable();
            $table->text('sports')->nullable();
            $table->text('positions')->nullable();
            $table->dateTime('player_last_updated')->nullable();
            $table->string('address_first_line', 128)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player');
    }
}
