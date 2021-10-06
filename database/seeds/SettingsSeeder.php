<?php

use App\Enums\TransactionKey;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    protected array $settings = [
        [
            'name' => TransactionKey::REGISTRATION_DEPOSIT,
            'value' => 100000,
        ],
        [
            'name' => TransactionKey::ARTICLE_CREATION_WITHDRAW,
            'value' => 5000,
        ],
        [
            'name' => TransactionKey::COMMENT_CREATION_WITHDRAW,
            'value' => 5000,
        ],
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
