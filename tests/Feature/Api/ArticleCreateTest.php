<?php

namespace Tests\Feature\Api;

use App\Article;
use App\Enums\SettingKey;
use App\Enums\TransactionType;
use App\Setting;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ArticleCreateTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_return_forbidden_error_when_trying_to_add_article_without_enough_charge()
    {
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => -1000]);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $response = $this->postJson('/api/articles', $data, $this->headers);

        $response->assertStatus(403)
            ->assertJson([
                'errors' => [
                    'message' => 'Not Enough Charge',
                    'status_code' => 403
                ]
            ]);
    }

    /** @test */
    public function it_returns_the_article_on_successfully_creating_a_new_article()
    {
        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $response = $this->postJson('/api/articles', $data, $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'article' => [
                    'slug' => 'test-title',
                    'title' => 'test title',
                    'description' => 'test description',
                    'body' => 'test body with random text',
                    'tagList' => [],
                    'favorited' => false,
                    'favoritesCount' => 0,
                    'author' => [
                        'username' => $this->loggedInUser->username,
                        'bio' => $this->loggedInUser->bio,
                        'image' => $this->loggedInUser->image,
                        'following' => false,
                    ]
                ]
            ]);

        $data['article']['tagList'] = ['test', 'coding'];

        $response = $this->postJson('/api/articles', $data, $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'article' => [
                    'slug' => 'test-title-1',
                    'title' => 'test title',
                    'tagList' => ['test', 'coding'],
                    'author' => [
                        'username' => $this->loggedInUser->username,
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_decrease_charge_of_user_on_creating_a_new_article()
    {
        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);

        $this->assertDatabaseHas('users', [
            'username' => $this->loggedInUser->username,
            'email' => $this->loggedInUser->email,
            'charge' => Setting::get(SettingKey::REGISTRATION_DEPOSIT) - Setting::get(SettingKey::ARTICLE_CREATION_WITHDRAW)
        ]);
    }

    /** @test */
    public function it_create_transaction_and_factor_on_creating_a_new_article()
    {
        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->loggedInUser->id,
            'amount' => Setting::get(SettingKey::ARTICLE_CREATION_WITHDRAW),
            'type' => TransactionType::WITHDRAWAL,
        ]);

        $transaction = Transaction::query()->latest('id')->first();
        $product = Article::query()->latest('id')->first();
        $this->assertDatabaseHas('factors', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_type' => Article::class,
        ]);
    }

    /** @test */
    public function it_return_forbbiden_resoponse_for_second_request_if_charge_is_not_enough_for_two_request()
    {
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 3000]);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers)->assertStatus(200);

        $response = $this->postJson('/api/articles', $data, $this->headers);
        $response->assertStatus(403)
            ->assertJson([
                'errors' => [
                    'message' => 'Not Enough Charge',
                    'status_code' => 403
                ]
            ]);
    }

    /** @test */
    public function it_returns_appropriate_field_validation_errors_when_creating_a_new_article_with_invalid_inputs()
    {
        $data = [
            'article' => [
                'title' => '',
                'description' => '',
            ]
        ];

        $response = $this->postJson('/api/articles', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'title' => ['field is required.'],
                    'description' => ['field is required.'],
                    'body' => ['field is required.'],
                ]
            ]);

        $data['article']['tagList'] = 'invalid tag';

        $response = $this->postJson('/api/articles', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'tagList' => ['list must be an array.'],
                ]
            ]);
    }

    /** @test */
    public function it_returns_an_unauthorized_error_when_trying_to_add_article_without_logging_in()
    {
        $response = $this->postJson('/api/articles', []);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_disable_user_if_charge_become_negative_after_create_article()
    {
        Mail::fake();
        $this->loggedInUser->update(['charge' => 3000]);


        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];
        $this->postJson('/api/articles', $data, $this->headers);

        $user = User::query()->whereKey($this->loggedInUser->id)->first();
        $this->assertNotNull($user->disabled_at);
    }

    /** @test */
    public function it_return_forbidden_response_if_disabled_user_want_to_create_article()
    {
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['disabled_at' => now()]);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];
        $this->postJson('/api/articles', $data, $this->headers)
            ->assertStatus(403);
    }
}
