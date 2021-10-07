<?php

namespace Tests;

use App\Enums\SettingKey;
use App\Services\TransactionService;
use App\Setting;
use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $loggedInUser;

    protected $user;

    protected $headers;

    public function setUp()
    {
        parent::setUp();

        (new \DatabaseSeeder())->call(\SettingsSeeder::class);
        $users = factory(\App\User::class)->times(2)
            ->create(['is_admin' => 1])
            ->each(function (User $user) {
                (new TransactionService())
                    ->deposit($user, Setting::get(SettingKey::REGISTRATION_DEPOSIT));
            });

        $this->loggedInUser = $users[0];

        $this->user = $users[1];

        $this->headers = [
            'Authorization' => "Bearer {$this->loggedInUser->token}"
        ];
    }
}
