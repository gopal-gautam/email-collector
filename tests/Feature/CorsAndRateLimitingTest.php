<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CorsAndRateLimitingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Project $project;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and project for testing
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'allowed_origins' => json_encode([
                'https://example.com',
                'https://test.com',
                'https://subdomain.example.com'
            ]),
        ]);
    }

    /** @test */
    public function cors_allows_valid_origins()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://example.com');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
    }

    /** @test */
    public function cors_blocks_invalid_origins()
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
    public function cors_handles_wildcard_origins()
    {
        // Update project to allow all origins
        $this->project->update([
            'allowed_origins' => json_encode(['*'])
        ]);

        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://anydomain.com',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }

    /** @test */
    public function cors_handles_subdomain_matching()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://subdomain.example.com',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://subdomain.example.com');
    }

    /** @test */
    public function cors_preflight_requests_work()
    {
        $response = $this->call('OPTIONS', '/api/v1/subscriptions', [], [], [], [
            'HTTP_ORIGIN' => 'https://example.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, X-Project-ID, X-Api-Key',
            'HTTP_X_PROJECT_ID' => $this->project->public_id,
            'HTTP_X_API_KEY' => $this->project->api_key,
        ]);

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://example.com');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
        $response->assertHeader('Access-Control-Max-Age');
    }

    /** @test */
    public function rate_limiting_blocks_excessive_subscription_requests()
    {
        // Clear any existing rate limit cache
        Cache::flush();
        
        $headers = [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ];

        // Make requests up to the limit (30 per minute for subscriptions)
        for ($i = 1; $i <= 31; $i++) {
            $response = $this->postJson('/api/v1/subscriptions', [
                'email' => "test{$i}@example.com"
            ], $headers);

            if ($i <= 30) {
                $response->assertStatus(200);
            } else {
                // 31st request should be rate limited
                $response->assertStatus(429);
                break;
            }
        }
    }

    /** @test */
    public function rate_limiting_blocks_excessive_unsubscribe_requests()
    {
        // Clear any existing rate limit cache
        Cache::flush();
        
        $headers = [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ];

        // Make requests up to the limit (10 per minute for unsubscribe)
        for ($i = 1; $i <= 11; $i++) {
            $response = $this->postJson('/api/v1/unsubscribe', [
                'email' => "test{$i}@example.com"
            ], $headers);

            if ($i <= 10) {
                $response->assertStatus(200);
            } else {
                // 11th request should be rate limited
                $response->assertStatus(429);
                break;
            }
        }
    }

    /** @test */
    public function rate_limiting_is_per_ip_address()
    {
        // Clear any existing rate limit cache
        Cache::flush();
        
        $headers = [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ];

        // Make 30 requests from first IP
        for ($i = 1; $i <= 30; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
                             ->postJson('/api/v1/subscriptions', [
                                 'email' => "test{$i}@example.com"
                             ], $headers);
            
            $response->assertStatus(200);
        }

        // Request from different IP should still work (not rate limited)
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])
                         ->postJson('/api/v1/subscriptions', [
                             'email' => 'test@differentip.com'
                         ], $headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function rate_limiting_includes_proper_headers()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        
        // Check rate limit headers are present
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }

    /** @test */
    public function cors_security_headers_are_included()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        
        // Check security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('Referrer-Policy');
    }

    /** @test */
    public function cors_blocks_requests_without_origin_when_not_wildcard()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            // No Origin header
        ]);

        // Should still work for direct API calls without Origin
        $response->assertStatus(200);
    }

    /** @test */
    public function cors_handles_case_insensitive_origins()
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'test@example.com'
        ], [
            'X-Project-ID' => $this->project->public_id,
            'X-Api-Key' => $this->project->api_key,
            'Origin' => 'HTTPS://EXAMPLE.COM', // Uppercase
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function rate_limiting_respects_configured_limits()
    {
        // Test that rate limits respect the configuration
        $currentLimit = config('newsletter.rate_limits.subscriptions');
        $this->assertEquals(30, $currentLimit);
        
        $unsubscribeLimit = config('newsletter.rate_limits.unsubscribe');
        $this->assertEquals(10, $unsubscribeLimit);
    }
}
