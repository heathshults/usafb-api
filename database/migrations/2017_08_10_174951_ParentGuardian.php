<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParentGuardian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parent_guardian', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobile_phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('last_name', 80)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('work_phone', 20)->nullable();
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
        Schema::dropIfExists('parent_guardian');
    }
}
