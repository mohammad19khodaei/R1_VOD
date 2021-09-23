<?php

namespace App\RealWorld\Transformers;

class UserFactorTransformer extends Transformer
{
    protected $resourceName = 'factors';

    public function transform($data)
    {
        return [
            'product_id' => $data['product_id'],
            'product_type' => $data['product_type'],
            'factor_number' => $data['factor_number'],
            'amount' => $data['transaction']['amount'],
        ];
    }
}