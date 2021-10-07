<?php

namespace App\Http\Requests\Api;


class UpdateSetting extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'value' => ['required', 'integer']
        ];
    }
}
