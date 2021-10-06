<?php

namespace App\RealWorld\Transformers;

class SettingTransformer extends Transformer
{
    protected $resourceName = 'setting';

    public function transform($data)
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'value' => $data['value'],
        ];
    }
}