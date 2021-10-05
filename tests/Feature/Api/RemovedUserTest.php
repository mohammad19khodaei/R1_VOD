<?php

namespace Tests\Feature\Api;

use App\Jobs\RemoveDisabledUserJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RemovedUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queued_a_job_for_removing_disabled_user()
    {
        Queue::fake();
        $this->user->update(['charge' => -1000]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'charge' => -1000,
            'disabled_at' => now()
        ]);

        Queue::assertPushed(RemoveDisabledUserJob::class, function ($job) {
            return $job->user->id = $this->user->id;
        });
    }
}