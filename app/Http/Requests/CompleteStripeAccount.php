<?php

namespace App\Http\Requests;

class CompleteStripeAccount extends ApiRequest
{
    public function rules()
    {
        return [
            'first_name' => 'bail|required|string',
            'last_name' => 'bail|required|string',
            'ssn_last_4' => 'bail|required|string',
            'phone' => 'bail|required|string',
            'dob' => 'bail|required|string',
            'city' => 'bail|required|string',
            'state' => 'bail|required|string',
            'country' => 'bail|required|string',
            'address_line_one' => 'bail|required|string',
            'postal_code' => 'bail|required|string',
            // 'front_image' => 'bail|required|file',
            // 'back_image' => 'bail|required|file'
        ];
    }
}
