<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->longText('comment');
            $table->integer('mark');

            $table->timestamps();
        });
        Schema::create('product_review',function (Blueprint $table){
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('review_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
            $table->foreign('review_id')
                ->references('id')
                ->on('reviews')
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
        Schema::dropIfExists('product_review');
        Schema::dropIfExists('reviews');
    }
}
