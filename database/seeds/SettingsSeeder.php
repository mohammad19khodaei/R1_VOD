<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    protected array $settings = [
        [
            'name' => 'registration_deposit',
            'value' => 100000,
        ],
        [
            'name' => 'article_create_withdraw',
            'value' => 5000,
        ],
        [
            'name' => 'registration_withdraw',
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
