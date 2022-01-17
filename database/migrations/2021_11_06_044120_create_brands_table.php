<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image');
            $table->boolean('status')->nullable();
            $table->timestamps();
        });

        Schema::create('brand_product',function (Blueprint $table){
            $table->primary(['brand_id','product_id']);

            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
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
        Schema::dropIfExists('brand_product');
        Schema::dropIfExists('brands');
    }
}
