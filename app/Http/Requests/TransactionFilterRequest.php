<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionFilterRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'per_page'        => 'nullable|integer',
            'page'            => 'nullable|integer',

            // validation for transaction filter
            'total_amount'    => 'nullable|integer',
            'name'            => 'nullable|max:255',
        ];
    }
}
