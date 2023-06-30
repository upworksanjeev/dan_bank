<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeFeesInTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('stripe_fees_type')->nullable()->after('platform_share');
            $table->double('stripe_fees_percentage')->default(0)->after('stripe_fees_type');
            $table->double('stripe_fees_share')->default(0)->after('stripe_fees_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['stripe_fees_type', 'stripe_fees_percentage', 'stripe_fees_share']);
        });
    }
}
