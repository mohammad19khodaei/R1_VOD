<?php

namespace Tests;

use App\Enums\TransactionAmount;
use App\Services\TransactionService;
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

        $users = factory(\App\User::class)->times(2)
            ->create()
            ->each(function (User $user) {
                (new TransactionService())
                    ->deposit($user, TransactionAmount::REGISTRATION_DEPOSIT);
            });

        $this->loggedInUser = $users[0];

        $this->user = $users[1];

        $this->headers = [
            'Authorization' => "Bearer {$this->loggedInUser->token}"
        ];
    }
}
