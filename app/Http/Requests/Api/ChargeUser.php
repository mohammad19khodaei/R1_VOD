<?php

namespace App\Http\Requests\Api;

class ChargeUser extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => ['required', 'integer', 'min:5000']
        ];
    }
}
