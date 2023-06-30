<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Setting;

class AddStripeFeeColumnsInSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $setting = new Setting;
        $setting->setting_id = (String) Str::uuid();
        $setting->title = "stripe_fees_type";
        $setting->values = "fixed";
        $setting->save();

        $setting = new Setting;
        $setting->setting_id = (String) Str::uuid();
        $setting->title = "stripe_fee_percent";
        $setting->values = "5";
        $setting->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('title', 'stripe_fees_type')->orWhere('title', 'stripe_fee_percent')->delete();
    }
}
