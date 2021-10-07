<?php

namespace App\RealWorld\Transformers;

class UserTransformer extends Transformer
{
    protected $resourceName = 'user';

    public function transform($data)
    {
        return [
            'email' => $data['email'],
            'token' => $data['token'],
            'username' => $data['username'],
            'bio' => $data['bio'],
            'image' => $data['image'],
            'charge' => $data['charge'],
            'is_admin' => $data['is_admin']
        ];
    }
}