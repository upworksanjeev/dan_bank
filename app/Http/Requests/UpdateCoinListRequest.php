<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoinListRequest extends ApiRequest
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
            'amount'     => 'required|integer',
            'logo_image' => 'nullable|mimes:jpg,jpeg,png',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,category_id'
        ];
    }
}
