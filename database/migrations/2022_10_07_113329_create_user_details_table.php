<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_detail_id')->unique();
            $table->string('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->update('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('ssn_last_4')->default(0);
            $table->string('phone')->nullable();
            $table->string('dob')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('address_line_one')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->text('stripe_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
