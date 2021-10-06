<?php

namespace App\Services;

use App\Setting;

class SettingService
{
    public function updateSetting(Setting $setting, array $parameters)
    {
        $setting = tap($setting)->update($parameters);

        cache()->forever("setting_{$setting->name}", $parameters['value']);

        return $setting;
    }
}