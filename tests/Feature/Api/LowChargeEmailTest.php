<?php

namespace Tests\Feature\Api;

use App\Comment;
use App\Mail\LowUserCharge;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LowChargeEmailTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function it_send_low_charge_email_when_charge_become_low_after_create_article()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 24000]);
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
        Mail::assertQueued(LowUserCharge::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userId,
            'in_progress' => 1
        ]);
    }

    /** @test */
    public function it_send_low_charge_email_when_charge_become_low_after_create_article_only_once()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 24000]);
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
        Mail::assertQueued(LowUserCharge::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userId,
            'in_progress' => 1
        ]);

        $data = [
            'article' => [
                'title' => 'test title 2',
                'description' => 'test description 2',
                'body' => 'test body with random text 2',
            ]
        ];

        $this->postJson('/api/articles', $data, $this->headers);
        Mail::assertQueued(LowUserCharge::class, 1);
    }

    /** @test */
    public function it_send_low_charge_email_again_when_after_charge__user_charge_become_low_again()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 24000]);
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
        Mail::assertQueued(LowUserCharge::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userId,
            'in_progress' => 1
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
        Mail::assertQueued(LowUserCharge::class, 2);
    }

    /** @test */
    public function it_send_low_charge_email_when_charge_become_low_after_add_none_free_comment()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 24000]);
        User::setEventDispatcher($dispatcher);

        $article = $this->user->articles()->save(factory(\App\Article::class)->make());
        $article
            ->comments()
            ->saveMany(
                factory(Comment::class)
                    ->times(Comment::MAX_NUMBER_OF_FREE_COMMENT)
                    ->make(['user_id' => $this->loggedInUser->id])
            );

        $data = [
            'comment' => [
                'body' => 'This is a comment'
            ]
        ];
        $this->postJson("/api/articles/{$article->slug}/comments", $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertQueued(LowUserCharge::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userId,
            'in_progress' => 1
        ]);
    }

    /** @test */
    public function it_not_send_low_charge_email_when_add_free_comment()
    {
        $dispatcher = User::getEventDispatcher();
        User::unsetEventDispatcher();
        $this->loggedInUser->update(['charge' => 24000]);
        User::setEventDispatcher($dispatcher);

        $article = $this->user->articles()->save(factory(\App\Article::class)->make());
        $article
            ->comments()
            ->saveMany(
                factory(Comment::class)
                    ->times(Comment::MAX_NUMBER_OF_FREE_COMMENT - 1)
                    ->make(['user_id' => $this->loggedInUser->id])
            );

        $data = [
            'comment' => [
                'body' => 'This is a comment'
            ]
        ];
        $this->postJson("/api/articles/{$article->slug}/comments", $data, $this->headers);

        $userId = $this->loggedInUser->id;
        Mail::assertNotQueued(LowUserCharge::class, fn($mail) => $mail->user->id = $userId);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $userId,
            'in_progress' => 1
        ]);
    }
}