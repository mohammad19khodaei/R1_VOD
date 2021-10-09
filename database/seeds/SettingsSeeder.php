<?php

use App\Enums\SettingKey;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    protected array $settings = [
        [
            'name' => SettingKey::REGISTRATION_DEPOSIT,
            'value' => 100000,
        ],
        [
            'name' => SettingKey::ARTICLE_CREATION_WITHDRAW,
            'value' => 5000,
        ],
        [
            'name' => SettingKey::COMMENT_CREATION_WITHDRAW,
            'value' => 5000,
        ],
        [
            'name' => SettingKey::NOTIFY_USER_BALANCE_THRESHOLD,
            'value' => 20000,
        ],
        [
            'name' => SettingKey::MAX_NUMBER_OF_FREE_COMMENT,
            'value' => 5,
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->settings as $setting) {
            \App\Setting::query()
                ->firstOrCreate(
                    [
                        'name' => $setting['name']
                    ],
                    [
                        'value' => $setting['value'],
                    ]
                );
        }
    }
}
