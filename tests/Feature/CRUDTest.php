<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Validation\ValidationException;

/**
 * CRUD Test
 * - On this test we will cover everything that involves a CRUD workflow:
 *
 * 1. Create a route to create a blog
 * 2. Validate the payload
 * 3. create a route to update a blog
 * 4. Use policies
 * 5. Use soft delete
 * 6. Use middleware
 * 7. Dispatch an event
 * 8. Create a listener and a Mailable
 *
 * @package Tests\Feature\Exam
 */
class CRUDTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create Blog
     *
     * @test
     */
    public function create_a_blog()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('blogs.store'), [
            'name' => 'Laravel Blog',
            'domain' => 'https://blog.example.com'
        ]);

        $this->assertDatabaseHas('blogs', [
            'name' => 'Laravel Blog',
            'domain' => 'https://blog.example.com',
            'owner_id' => $user->id
        ]);
    }

    /**
     * Validates the payload
     * - name: should be required
     * - domain : should be required and have a valid url
     *
     * @test
     */
    public function validate_the_payload()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->expectException(ValidationException::class);
        $nameInvalid = $this->actingAs($user)->post(route('blogs.store'), [
            'name' => '',
            'domain' => 'https://blog.example.com'
        ]);

        $this->expectException(ValidationException::class);
        $domainInvalid = $this->actingAs($user)->post(route('blogs.store'), [
            'name' => 'Laravel Blog',
            'domain' => 'blogdomaincom',
        ]);
        
        $this->expectException(ValidationException::class);
        $ownerNotAllowToChange = $this->actingAs($user)->post(route('blogs.store'), [
            'name' => 'Laravel Blog',
            'domain' => 'https://blog.example.com',
            'owner_id' => $user2->id,
        ]);
        
    }
    /**
     * Update a blog
     * @test
     */
    public function update_a_blog()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        
        $blog = Blog::factory()->create(['owner_id' => $user->id]);
        $this->actingAs($user);
        
        $this->put(route('blogs.update', $blog), [
            'name' => 'Laravel Blog',
            'domain' => 'https://new.example.com'
        ]);

        $blog->refresh();
        
        $this->assertDatabaseHas('blogs', [
            'id'  => $blog->id,
            'name' => 'Laravel Blog',
            'domain' => 'https://new.example.com',
            'owner_id' => $user->id
        ]);
    }

    /**
     * Create a Policy to authorize only the owner
     * of the blog to be able to perform actions on the blog.
     *
     * @test
     */
    public function use_policy_to_authorize_actions()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $blog = Blog::factory()->create([
            'owner_id' => $user2->id,
        ]);

        $this->actingAs($user1)->delete(route('blogs.delete', $blog))->assertForbidden();
        $this->actingAs($user1)->get(route('blogs.show', $blog))->assertForbidden();
        $this->actingAs($user1)->put(route('blogs.update', $blog))->assertForbidden();
    }
    
    /**
     * Apply Soft Delete
     *
     * @test
     */
    public function apply_soft_delete()
    {
        $user = User::factory()->create();

        $blog = Blog::factory()->create([
            'owner_id' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('blogs.delete', $blog));
        $this->assertSoftDeleted('blogs', ['id' => $blog->id]);
    }

    /**
     * Create a middleware that will block a user with
     * a name "Blocked User" to create Blogs
     *
     * @test
     */
    public function use_middleware_to_block_access()
    {
        $user = User::factory()->create([
            'name' => 'Blocked User',
        ]);

        $this->actingAs($user)->post(route('blogs.store'), [
            'name' => 'Laravel Blog',
            'domain' => 'https://blog.example.com'
        ])->assertUnauthorized();
    }

    /**
     * Dispatch an event after a creation of a Blog
     *
     * @test
     */
    public function dispatch_an_event()
    {
        $this->withoutExceptionHandling();
        Event::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('blogs.store'), [
            'name' => 'Laravel Blog',
            'domain' => 'https://blog.example.com'
        ]);

        Event::assertDispatched(\App\Events\BlogCreated::class);
    }

    /**
     * When a Blog is created and event will be dispatched,
     * a listener should be created that will be used to
     * send an email to the creator with Blog details
     *
     * @test
     */
    public function create_a_listener_that_will_send_an_email_to_user()
    {
        $this->withoutExceptionHandling();
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('blogs.store'), [
                'name' => 'Laravel Blog',
                'domain' => 'https://blog.example.com'
            ]);

        Mail::assertSent(\App\Mail\NewBlogCreated::class);
    }
}