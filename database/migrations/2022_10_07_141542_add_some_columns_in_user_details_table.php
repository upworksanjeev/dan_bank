<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsInUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('account_holder_name')->nullable()->after('stripe_response');
            $table->string('account_number')->nullable()->after('account_holder_name');
            $table->string('routing_number')->nullable()->after('account_number');
            $table->text('bank_account_stripe_response')->nullable()->after('routing_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn(['account_holder_name', 'account_number', 'routing_number', 'bank_account_stripe_response']);
        });
    }
}
