<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('coin_id')->nullable();
            $table->foreign('coin_id')->references('coin_id')->on('coins')->onDelete('cascade')->update('cascade');
            $table->enum('status', ['completed', 'pending', 'cancelled'])->nullable();
            $table->double('total_amount')->default(0);
            $table->double('platform_percentage')->default(0);
            $table->double('platform_share')->default(0);
            $table->double('remaining_amount')->default(0);
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
        Schema::dropIfExists('transactions');
    }
}
