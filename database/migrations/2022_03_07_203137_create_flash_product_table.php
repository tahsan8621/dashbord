<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlashProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flash_product', function (Blueprint $table) {
            $table->primary(['product_id','flash_id']);

            $table->unsignedBigInteger('flash_id');
            $table->unsignedBigInteger('product_id');


            $table->foreign('flash_id')
                ->references('id')
                ->on('flashes')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
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
        Schema::dropIfExists('flash_product');
    }
}
