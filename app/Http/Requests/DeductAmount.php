<?php

namespace App\Http\Requests;

class DeductAmount extends ApiRequest
{
    public function rules()
    {
        return [
            'status' => 'bail|required',
        ];
    }
}
