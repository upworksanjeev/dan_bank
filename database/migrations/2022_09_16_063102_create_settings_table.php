<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Setting;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_id')->unique();
            $table->string('title')->nullable();
            $table->text('values')->nullable();
            $table->timestamps();

        });
        $setting = new Setting;
        $setting->setting_id = (String) Str::uuid();
        $setting->title = "fees_type";
        $setting->values = "fixed";
        $setting->save();

        $setting = new Setting;
        $setting->setting_id = (String) Str::uuid();
        $setting->title = "fee_percent";
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
        Schema::dropIfExists('settings');
    }
}
