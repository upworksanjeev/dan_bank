<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_categories', function (Blueprint $table) {
            $table->id();
            $table->string('coin_category_id')->unique();
            $table->string('category_id')->nullable();
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('cascade')->update('cascade');
            $table->string('coin_listing_id')->nullable();
            $table->foreign('coin_listing_id')->references('coin_listing_id')->on('coin_listings')->onDelete('cascade')->update('cascade');
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
        Schema::dropIfExists('coin_categories');
    }
}
