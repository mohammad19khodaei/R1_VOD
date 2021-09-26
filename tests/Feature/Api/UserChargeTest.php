<?php

namespace Tests\Feature\Api;

use App\Enums\TransactionType;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserChargeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_increase_user_charge_and_remove_in_progress_notification()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 19000]);
        User::setEventDispatcher($dispatcher);

        $this->loggedInUser->notifications()->create();

        $data = ['amount' => 10000];
        $this->postJson('/api/user/charge', $data, $this->headers)
            ->assertStatus(200);

        $userId = $this->loggedInUser->id;
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'charge' => 19000 + 10000,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userId,
            'in_progress' => 0,
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

    /** @test */
    public function it_create_deposit_transaction_on_charge_account()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => -1000, 'disabled_at' => now()]);
        User::setEventDispatcher($dispatcher);

        $data = ['amount' => 10000];
        $this->postJson('/api/user/charge', $data, $this->headers);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->loggedInUser->id,
            'type' => TransactionType::DEPOSIT,
            'amount' => $data['amount'],
        ]);
    }
}
