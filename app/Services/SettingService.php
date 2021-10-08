<?php

namespace App\Services;

use App\Setting;

class SettingService
{
    public function updateSetting(Setting $setting, array $parameters)
    {
        $setting = tap($setting)->update($parameters);

        cache()->forget("setting_{$setting->name}");

        return $setting;
    }

    public function get(string $key, int $default = 0): int
    {
        return cache()->rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = Setting::query()->where('name', $key)->first();

            return $setting->value ?? $default;
        });
    }
}