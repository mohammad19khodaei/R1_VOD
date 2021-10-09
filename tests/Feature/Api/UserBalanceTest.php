<?php

namespace Tests\Feature\Api;

use App\Enums\TransactionType;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserBalanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_successfully_does_charge_user_account_proccess()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 19000]);
        User::setEventDispatcher($dispatcher);

        $this->loggedInUser->emailHistories()->create();

        $data = ['amount' => 10000];
        $this->postJson('/api/user/charge', $data, $this->headers)
            ->assertStatus(200);

        $userId = $this->loggedInUser->id;
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'balance' => 19000 + 10000,
        ]);
        $this->assertDatabaseHas('email_histories', [
            'user_id' => $userId,
            'in_progress' => 0,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $userId,
            'amount' => $data['amount'],
        ]);
    }

    /** @test */
    public function it_return_validation_error_for_invalid_amount()
    {
        $data = ['amount' => -1000];
        $this->postJson('/api/user/charge', $data, $this->headers)
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'amount' => ['must be at least 5000.']
                ]
            ]);
    }
}