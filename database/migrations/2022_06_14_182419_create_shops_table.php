<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->unsignedBigInteger('seller_id');
            $table->longtext('shop_details')->nullable();
            $table->string('shop_header_banner')->nullable();
            $table->string('shop_main_banner')->nullable();
            $table->string('shop_fb_link')->nullable();
            $table->string('shop_tw_link')->nullable();
            $table->string('name',250)->unique();
            $table->boolean('authorized')->default(0);
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
        Schema::dropIfExists('shops');
    }
}
