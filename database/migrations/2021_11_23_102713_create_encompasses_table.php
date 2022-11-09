<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncompassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encompasses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('bins');
            $table->string('truckName');
            $table->string('category')->nullable(true);
            $table->longText('binName')->nullable(true);
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
        Schema::dropIfExists('encompasses');
    }
}
