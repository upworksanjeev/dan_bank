<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('coin_id')->unique();
            $table->string('from')->nullable();
            $table->foreign('from')->references('user_id')->on('users')->onDelete('cascade')->update('cascade');
            $table->string('to')->nullable();
            $table->foreign('to')->references('user_id')->on('users')->onDelete('cascade')->update('cascade');
            $table->enum('event_name', ['none', 'birthday', 'achievement', 'just_cause', 'anniversary'])->nullable();
            $table->double('from_latitude')->default(0);
            $table->double('from_longitude')->default(0);
            $table->string('reason')->nullable();
            $table->double('amount')->default(0);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->nullable();
            $table->text('stripe_response')->nullable();
            $table->bigInteger('flags')->default(0);
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
        Schema::dropIfExists('coins');
    }
}
