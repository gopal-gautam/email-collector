<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Jobs\SendConfirmationEmail;
use App\Jobs\SendWelcomeEmail;
use App\Jobs\SendAdminNotification;

class ApiSubscriptionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Project $project;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        Queue::fake();
        
        // Create a user and project for testing
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'allowed_origins' => json_encode(['https://example.com', 'https://test.com']),
            'double_opt_in' => true,
            'welcome_email' => true,
            'admin_notifications' => true,
        ]);
    }

    /** @test */
    public function health_check_endpoint_returns_ok()
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
                 ->assertJson(['ok' => true]);
    }

    /** @test */
    public function subscription_requires_project_authentication()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function subscription_requires_valid_project_id()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => 'invalid-project-id',
            'X-Api-Key' => 'invalid-api-key',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function subscription_requires_valid_api_key()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => 'invalid-api-key',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function successful_subscription_with_double_opt_in()
    {
        $email = 'test@example.com';
        
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => $email
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'email' => $email,
                         'status' => 'pending',
                         'requires_confirmation' => true,
                     ]
                 ]);

        // Check database
        $this->assertDatabaseHas('subscriptions', [
            'project_id' => $this->project->id,
            'email' => $email,
            'status' => 'pending',
        ]);

        // Check queue jobs
        Queue::assertPushed(SendConfirmationEmail::class);
        Queue::assertNotPushed(SendWelcomeEmail::class);
        Queue::assertPushed(SendAdminNotification::class);
    }

    /** @test */
    public function successful_subscription_without_double_opt_in()
    {
        $this->project->update(['double_opt_in' => false]);
        $email = 'test@example.com';
        
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => $email
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'email' => $email,
                         'status' => 'subscribed',
                         'requires_confirmation' => false,
                     ]
                 ]);

        // Check database
        $this->assertDatabaseHas('subscriptions', [
            'project_id' => $this->project->id,
            'email' => $email,
            'status' => 'subscribed',
        ]);

        // Check queue jobs
        Queue::assertNotPushed(SendConfirmationEmail::class);
        Queue::assertPushed(SendWelcomeEmail::class);
        Queue::assertPushed(SendAdminNotification::class);
    }

    /** @test */
    public function subscription_validates_email_format()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'invalid-email'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors' => ['email']
                 ]);
    }

    /** @test */
    public function subscription_blocks_disposable_email_domains()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@10minutemail.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors' => ['email']
                 ]);
    }

    /** @test */
    public function duplicate_subscription_returns_success_idempotent()
    {
        $email = 'test@example.com';
        
        // Create initial subscription
        Subscription::create([
            'project_id' => $this->project->id,
            'email' => $email,
            'status' => 'subscribed',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => $email
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'email' => $email,
                         'status' => 'subscribed',
                     ]
                 ]);

        // Should only have one subscription record
        $this->assertEquals(1, Subscription::where('email', $email)->count());
    }

    /** @test */
    public function subscription_fails_for_inactive_project()
    {
        $this->project->update(['status' => 'inactive']);
        
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function subscription_fails_for_invalid_cors_origin()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://malicious.com',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function subscription_logs_api_request()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200);

        // Check API request was logged
        $this->assertDatabaseHas('api_requests', [
            'project_id' => $this->project->id,
            'path' => '/api/v1/subscriptions',
            'method' => 'POST',
            'status_code' => 200,
        ]);
    }

    /** @test */
    public function unsubscribe_endpoint_works_correctly()
    {
        $email = 'test@example.com';
        
        // Create subscription first
        Subscription::create([
            'project_id' => $this->project->id,
            'email' => $email,
            'status' => 'subscribed',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->postJson('/api/v1/unsubscribe', [
            'email' => $email
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        // Always returns 200 for privacy
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Unsubscribe request processed'
                 ]);

        // Check database
        $this->assertDatabaseHas('subscriptions', [
            'project_id' => $this->project->id,
            'email' => $email,
            'status' => 'unsubscribed',
        ]);
    }

    /** @test */
    public function unsubscribe_returns_success_even_for_non_existent_email()
    {
        $response = $this->postJson('/api/v1/unsubscribe', [
            'email' => 'nonexistent@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        // Always returns 200 for privacy
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Unsubscribe request processed'
                 ]);
    }

    /** @test */
    public function subscription_includes_source_metadata()
    {
        $email = 'test@example.com';
        
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => $email,
            'source_url' => 'https://example.com/landing-page',
            'meta' => [
                'campaign' => 'summer-2024',
                'utm_source' => 'facebook'
            ]
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
            'Referer' => 'https://google.com',
            'User-Agent' => 'Mozilla/5.0 Test Browser',
        ]);

        $response->assertStatus(200);

        // Check metadata is stored
        $subscription = Subscription::where('email', $email)->first();
        $this->assertEquals('https://example.com/landing-page', $subscription->source_url);
        $this->assertEquals('https://google.com', $subscription->referrer);
        $this->assertEquals('Mozilla/5.0 Test Browser', $subscription->user_agent);
        $this->assertIsArray($subscription->meta);
        $this->assertEquals('summer-2024', $subscription->meta['campaign']);
    }
}
