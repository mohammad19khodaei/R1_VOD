<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'value'];

    public static function get(string $key, $default = null): int
    {
        return cache()->rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = self::query()->where('name', $key)->first();

            return $setting->value ?? $default;
        });
    }
}
