<?php

namespace App\Http\Requests;

class AddBankAccount extends ApiRequest
{
    public function rules()
    {
        return [
            'account_holder_name' => 'bail|required|string',
            'account_number' => 'bail|required|string',
            'routing_number' => 'bail|required|string'
        ];
    }
}
