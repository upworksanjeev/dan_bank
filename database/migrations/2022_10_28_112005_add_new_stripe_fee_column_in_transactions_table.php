<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewStripeFeeColumnInTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('stripe_fee_on_stripe_fee_type')->nullable()->after('fees_type');
            $table->double('stripe_fee_on_stripe_fee_value')->default(0)->after('stripe_fee_on_stripe_fee_type');
            $table->double('stripe_fee_on_stripe_fee_share')->default(0)->after('stripe_fee_on_stripe_fee_value');
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
            $table->dropColumn(['stripe_fee_on_stripe_fee_type', 'stripe_fee_on_stripe_fee_value', 'stripe_fee_on_stripe_fee_share']);
        });
    }
}
