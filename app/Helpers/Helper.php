<?php

if (!function_exists('remove_words')) {
    /**
     * Remove words from the start of a string.
     *
     * @param $text
     * @param int $count
     * @return string
     */
    function remove_words($text, $count = 1)
    {
        if (str_word_count($text) > $count) {
            return explode(' ', $text, $count + 1)[$count];
        }

        return '';
    }
}

if (!function_exists('setting')) {

    /**
     * fetch setting value
     *
     * @param $key
     * @param int $default
     * @return mixed
     */
    function setting($key, int $default = 0)
    {
        return app(\App\Services\SettingService::class)->get($key, $default);
    }
}
