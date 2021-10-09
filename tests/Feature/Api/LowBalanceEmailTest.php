<?php

namespace Tests\Feature\Api;

use App\Comment;
use App\Enums\NotificationType;
use App\Enums\SettingKey;
use App\Mail\LowUserBalance;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LowBalanceEmailTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function it_send_low_balance_email_when_balance_become_low_after_create_article()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 24000]);
        User::setEventDispatcher($dispatcher);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertQueued(LowUserBalance::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $userId,
            'type' => NotificationType::LOW_BALANCE_TYPE,
        ]);
    }

    /** @test */
    public function it_send_low_balance_email_when_balance_become_low_after_create_article_only_once()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 24000]);
        User::setEventDispatcher($dispatcher);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertQueued(LowUserBalance::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $userId,
            'type' => NotificationType::LOW_BALANCE_TYPE,
        ]);

        $data = [
            'article' => [
                'title' => 'test title 2',
                'description' => 'test description 2',
                'body' => 'test body with random text 2',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);
        Mail::assertQueued(LowUserBalance::class, 1);
    }

    /** @test */
    public function it_send_low_balance_email_again_when_after_user_balance_become_low_again()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 24000]);
        User::setEventDispatcher($dispatcher);

        $data = [
            'article' => [
                'title' => 'test title',
                'description' => 'test description',
                'body' => 'test body with random text',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertQueued(LowUserBalance::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $userId,
            'type' => NotificationType::LOW_BALANCE_TYPE,
        ]);

        // charge account
        $data = ['amount' => 5000];
        $this->postJson('api/user/charge', $data, $this->headers);

        $data = [
            'article' => [
                'title' => 'test title 2',
                'description' => 'test description 2',
                'body' => 'test body with random text 2',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);
        Mail::assertQueued(LowUserBalance::class, 2);
    }

    /** @test */
    public function it_send_low_balance_email_when_balance_become_low_after_add_none_free_comment()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 24000]);
        User::setEventDispatcher($dispatcher);

        $article = $this->user->articles()->save(factory(\App\Article::class)->make());
        $article
            ->comments()
            ->saveMany(
                factory(Comment::class)
                    ->times(setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT))
                    ->make(['user_id' => $this->loggedInUser->id])
            );

        $data = [
            'comment' => [
                'body' => 'This is a comment'
            ]
        ];
        $this->postJson("/api/articles/{$article->slug}/comments", $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertQueued(LowUserBalance::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $userId,
            'type' => NotificationType::LOW_BALANCE_TYPE,
        ]);
    }

    /** @test */
    public function it_not_send_low_balance_email_when_add_free_comment()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['balance' => 24000]);
        User::setEventDispatcher($dispatcher);

        $article = $this->user->articles()->save(factory(\App\Article::class)->make());
        $article
            ->comments()
            ->saveMany(
                factory(Comment::class)
                    ->times(setting(SettingKey::MAX_NUMBER_OF_FREE_COMMENT) - 1)
                    ->make(['user_id' => $this->loggedInUser->id])
            );

        $data = [
            'comment' => [
                'body' => 'This is a comment'
            ]
        ];
        $this->postJson("/api/articles/{$article->slug}/comments", $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertNotQueued(LowUserBalance::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseMissing('notification_logs', [
            'user_id' => $userId,
            'type' => NotificationType::LOW_BALANCE_TYPE,
        ]);
    }
}