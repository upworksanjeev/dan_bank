<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_medias', function (Blueprint $table) {
            $table->id();
            $table->string('coin_media_id')->unique();
            $table->string('coin_id')->nullable();
            $table->foreign('coin_id')->references('coin_id')->on('coins')->onDelete('cascade')->update('cascade');
            $table->enum('type', ['image', 'video', 'audio'])->nullable();
            $table->string('media_url')->nullable();
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
        Schema::dropIfExists('coin_medias');
    }
}
