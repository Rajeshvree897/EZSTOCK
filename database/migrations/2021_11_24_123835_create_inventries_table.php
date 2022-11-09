<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventries', function (Blueprint $table) {
            $table->id(); 
            $table->integer('truck_id');
            $table->integer('user_id');
            $table->integer('quantity');
            $table->string('item_code')->nullable(true);
            $table->string('item_name');
            $table->integer('bin_id')->nullable(true);
            $table->string('brand_name')->nullable(true);
            $table->string('basePN')->nullable(true);
            $table->integer('item_price')->nullable(true);
            $table->longText('description')->nullable(true);
            $table->longText('customer_details')->nullable(true);
            $table->string('setting')->nullable(true);
            $table->string('fix_quantity')->nullable(true);

            $table->string('item_image')->nullable(true);
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
        Schema::dropIfExists('inventries');
    }
}
