<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->string('friend_id')->unique();
            $table->string('user_one')->nullable();
            $table->foreign('user_one')->references('user_id')->on('users')->onDelete('cascade')->update('cascade');
            $table->string('user_two')->nullable();
            $table->foreign('user_two')->references('user_id')->on('users')->onDelete('cascade')->update('cascade');
            $table->bigInteger('flags')->default(0);
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
        Schema::dropIfExists('friends');
    }
}
