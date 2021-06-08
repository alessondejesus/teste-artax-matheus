<?php

namespace Tests\Exam;

use App\Jobs\CheckBlogsHealth;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

/**
 * Job Test
 * - On this we will check if you know how to:
 *
 * 1. Create a Job
 * 2. Send the Job to the Queue
 *
 * @package Tests\Feature\Exam
 */
class JobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a job that will check if blog is online
     * @test
     */
    public function create_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        Blog::factory()->count(2)->create(['owner_id' => $user->id]);

        CheckBlogsHealth::dispatch($user);

        Queue::assertPushed(CheckBlogsHealth::class);
    }

    /**
     * Making sure that the job is doing
     * what supposed to do
     * @test
     */
    public function make_sure_that_the_job_worked()
    {
        $user = User::factory()->create();
        Blog::factory()->create(['owner_id' => $user->id, 'domain' => 'blog.example.com']);
        Blog::factory()->create(['owner_id' => $user->id, 'domain' => 'invalid.example.com']);

        Http::fake([
            'https://blog.example.com' => Http::response('Hello World', 200),
            'https://invalid.example.com' => Http::response('', 500),
        ]);

        CheckBlogsHealth::dispatch($user);

        Notification::assertSentTo($user, \App\Notifications\BlogIsOffline::class);
    }
}