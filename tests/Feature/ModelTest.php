<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Models Test
 * - On this test we will check if you know how to:
 *
 * 1. Create a model
 * 2. Work with relationships
 * 3. Create Model Factories
 * 4. Use Get Mutators
 * 5. Use Set Mutators
 * 6. Casting properties
 * 7. Notification
 *
 * @package Tests\Feature\Exam
 */
class ModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create Blog Model
     *
     * @test
     */
    public function create_a_model()
    {
        $this->assertTrue(class_exists('App\Models\Blog'));
    }

    /**
     * Create relationships between User and Blogs
     *
     * @test
     */
    public function relationship_with_the_user()
    {
        $blog = new Blog();
        $relationship = $blog->owner();

        $this->assertEquals(BelongsTo::class, get_class($relationship), 'blogs->owner()');

        $blog = new User();
        $relationship = $blog->blogs();

        $this->assertEquals(HasMany::class, get_class($relationship), 'user->blogs()');
    }

    /**
     * Create factories for User and Blog
     *
     * @test
     */
    public function create_factories()
    {
        $user = User::factory()->create();
        Blog::factory()->create(['owner_id' => $user->id]);

        $this->assertCount(1, Blog::all());
    }
    
    /**
     * Create a get mutator on User's model to transform
     * the return from "joe doe" to "Joe Doe"
     *
     * @test
     */
    public function use_get_mutator()
    {
        $user = User::factory()->make();

        $user->name = 'joão silva';

        $this->assertEquals('João Silva', $user->name);
    }

    /**
     * Create a set mutator on User's model to transform
     * the password to a hash string when setting the password
     *
     * @test
     */
    public function use_set_mutator()
    {
        $user = User::factory()->make();

        $user->password = 'secret';

        $this->assertTrue(Hash::check('secret', $user->password));
    }

    /**
      * When we update a password of a user the same user should be
      * notified by email that his password was changed.
      *
      * @test
      */
    public function the_user_should_be_notified_after_a_password_change()
    {
        Notification::fake();

        $user = User::factory()->create();
        
        $user->password = 'secret';
        $user->save();

        Notification::assertSentTo($user, \App\Notifications\PasswordChanged::class);
    }
}