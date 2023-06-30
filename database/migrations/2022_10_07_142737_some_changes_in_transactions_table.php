<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SomeChangesInTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_id')->unique()->after('id');
            $table->string('fees_type')->nullable()->after('platform_share');
            $table->dropColumn('remaining_amount');
            $table->bigInteger('flags')->default(0)->after('stripe_response');
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
            $table->dropColumn(['fees_type', 'transaction_id', 'flags']);
            $table->double('remaining_amount')->nullable()->after('platform_share');
        });
    }
}
