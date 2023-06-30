<?php

namespace App\Http\Requests;

class StoreUser extends ApiRequest
{
    public function rules()
    {
        return [
            'name' => 'bail|required|string',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required|string',
            'phone' => 'bail|required|unique:users',
        ];
    }
}
