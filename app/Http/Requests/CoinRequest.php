<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoinRequest extends ApiRequest
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
            'friend_id'       => 'required|friends:friend_id,friend_id',   
            'event_name'      => 'required|max:255',   
            'latitude'        => 'required|numeric',   
            'longitude'       => 'required|numeric',   
            'reason'          => 'required',   
            'amount'          => 'required|integer',   
            'message'         => 'required',   
            'coin_image'      => 'required',   
            'location_name'   => 'required',   
        ];
    }
}
