<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Setting;
class AddNewStripeFeeColumnInSettingsTable extends Migration
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
        $setting->title = "stripe_fees_on_stripe_fees_type";
        $setting->values = "percentage";
        $setting->save();

        $setting = new Setting;
        $setting->setting_id = (String) Str::uuid();
        $setting->title = "stripe_fees_on_stripe_fees_value";
        $setting->values = "2.9";
        $setting->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('title', 'stripe_fees_on_stripe_fees_type')->orWhere('title', 'stripe_fees_on_stripe_fees_value')->delete();
    }
}
