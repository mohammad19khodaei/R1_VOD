<?php

namespace Tests\Feature\Api\Commands;

use App\Article;
use App\Comment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RemoveDisabledUsersCommandTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_remove_disabled_users_and_theirs_entities()
    {
        DB::statement('PRAGMA foreign_keys = ON;');
        $disabledUser = factory(User::class)->create(['disabled_at' => now()->subDays(2)->toDateTimeString()]);
        $disabledUser->articles()
            ->create(factory(Article::class)->raw());

        $this->user->articles()->create(factory(Article::class)->raw(['user_id' => $this->user->id]))
            ->comments()
            ->saveMany(
                factory(\App\Comment::class)
                    ->times(2)
                    ->make(['user_id' => $disabledUser->id])
            );

        $this->artisan('remove:disabled-users');

        $this->assertDatabaseMissing('users', [
            'id' => $disabledUser->id,
        ]);
        $this->assertDatabaseMissing('articles', [
            'user_id' => $disabledUser->id,
        ]);
        $this->assertDatabaseMissing('comments', [
            'user_id' => $disabledUser->id,
        ]);
    }
}