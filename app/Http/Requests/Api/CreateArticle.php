<?php

namespace App\Http\Requests\Api;

class CreateArticle extends ApiRequest
{
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->get('article') ?: [];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'body' => 'required|string',
            'tagList' => 'sometimes|array',
        ];
    }

    public function getParameters():array
    {
        return [
            'title' => $this->input('article.title'),
            'description' => $this->input('article.description'),
            'body' => $this->input('article.body'),
        ];
    }
}
