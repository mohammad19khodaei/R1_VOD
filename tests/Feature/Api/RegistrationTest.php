<?php

namespace Tests\Feature\Api;

use App\Enums\SettingKey;
use App\Enums\TransactionType;
use App\Setting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_user_with_token_on_valid_registration()
    {
        $data = [
            'user' => [
                'username' => 'test',
                'email' => 'test@test.com',
                'password' => 'secret',
            ]
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'email' => 'test@test.com',
                    'username' => 'test',
                    'bio' => null,
                    'image' => null,
                ]
            ]);

        $this->assertArrayHasKey('token', $response->json()['user'], 'Token not found');
    }

    /** @test */
    public function it_charge_user_account_on_valid_registration()
    {
        $data = [
            'user' => [
                'username' => 'test',
                'email' => 'test@test.com',
                'password' => 'secret',
            ]
        ];

        $this->postJson('/api/users', $data);

        $this->assertDatabaseHas('users', [
            'username' => 'test',
            'email' => 'test@test.com',
            'balance' => setting(SettingKey::REGISTRATION_DEPOSIT)
        ]);

        $user = User::query()->latest('id')->first();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => setting(SettingKey::REGISTRATION_DEPOSIT),
            'type' => TransactionType::DEPOSIT,
        ]);
    }

    /** @test */
    public function it_returns_field_required_validation_errors_on_invalid_registration()
    {
        $data = [];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'username' => ['field is required.'],
                    'email' => ['field is required.'],
                    'password' => ['field is required.'],
                ]
            ]);
    }

    /** @test */
    public function it_returns_appropriate_field_validation_errors_on_invalid_registration()
    {
        $data = [
            'user' => [
                'username' => 'invalid username',
                'email' => 'invalid email',
                'password' => '1',
            ]
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'username' => ['may only contain letters and numbers.'],
                    'email' => ['must be a valid email address.'],
                    'password' => ['must be at least 6 characters.'],
                ]
            ]);
    }

    /** @test */
    public function it_returns_username_and_email_taken_validation_errors_when_using_duplicate_values_on_registration()
    {
        $data = [
            'user' => [
                'username' => $this->user->username,
                'email' => $this->user->email,
                'password' => 'secret',
            ]
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'username' => ['has already been taken.'],
                    'email' => ['has already been taken.'],
                ]
            ]);
    }
}
