<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emailopens', function (Blueprint $table) {
            $table->string('Email');
            $table->string('Status');
            $table->integer('Type');
            $table->bigInteger('SentTime_ms')->nullable();
            $table->timestamp('OpenTime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emailopens');
    }
}
