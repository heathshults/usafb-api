<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDimDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {                    
        Schema::create('dim_dates', function (Blueprint $table) {
            $table->integer('id');
            $table->date('calendar_date');
            $table->integer('calendar_day');
            $table->integer('calendar_month');
            $table->integer('calendar_year');
            $table->integer('calendar_mon_d_y');
            $table->integer('calendar_m_d_y');
            $table->integer('reporting_y_q_w');
            $table->integer('reporting_y_w');
            $table->integer('reporting_y_w_d');
            $table->integer('day_of_week_number');
            $table->string('day_of_week_name');
            $table->string('day_of_week_short_name');
            $table->string('day_of_week_abv_name');
            $table->integer('week_number');
            $table->integer('week_of_month_number');
            $table->string('month_name');
            $table->string('month_short_name');
            $table->integer('month_number');
            $table->integer('day_of_month_number');
            $table->integer('day_of_quarter_number');
            $table->integer('year_number');
            $table->integer('day_of_year_number');
            $table->string('year_name');
            $table->string('week_short_name');
            $table->string('quarter_name');
            // set primary key - we'll generate our own unique key and NOT use an autoincrementing field (YYYYMMDD)
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dim_dates');
    }
}
