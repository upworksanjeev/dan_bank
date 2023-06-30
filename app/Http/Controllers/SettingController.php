<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::all();
        return api_success1($settings);
        return api_error();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $count = 0;
        if ($request->has('fees_type')) {
            $setting = Setting::where('title', 'fees_type')->first();
            $setting->values = $request->fees_type;
            if (!$setting->save()) $count++;
        }

        if ($request->has('fee_percent')) {
            $setting = Setting::where('title', 'fee_percent')->first();
            $setting->values = $request->fee_percent;
            if (!$setting->save()) $count++;
        }
        
        if ($request->has('stripe_fees_type')) {
            $setting = Setting::where('title', 'stripe_fees_type')->first();
            $setting->values = $request->stripe_fees_type;
            if (!$setting->save()) $count++;
        }
        
        if ($request->has('stripe_fees_on_stripe_fees_type')) {
            $setting = Setting::where('title', 'stripe_fees_on_stripe_fees_type')->first();
            $setting->values = $request->stripe_fees_on_stripe_fees_type;
            if (!$setting->save()) $count++;
        }
        
        if ($request->has('stripe_fee_percent')) {
            $setting = Setting::where('title', 'stripe_fee_percent')->first();
            $setting->values = $request->stripe_fee_percent;
            if (!$setting->save()) $count++;
        }
       
        if ($request->has('stripe_fees_on_stripe_fees_value')) {
            $setting = Setting::where('title', 'stripe_fees_on_stripe_fees_value')->first();
            $setting->values = $request->stripe_fees_on_stripe_fees_value;
            if (!$setting->save()) $count++;
        }

        if ($count) return api_success1('some fields are not Updated');
        return api_success1('Setting Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
