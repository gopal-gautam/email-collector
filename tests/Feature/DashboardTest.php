<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private User $otherUser;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable CSRF middleware for testing
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_can_view_projects_index()
    {
        $response = $this->actingAs($this->user)->get('/projects');
        
        $response->assertStatus(200)
                 ->assertViewIs('projects.index')
                 ->assertViewHas('projects');
    }

    /** @test */
    public function users_only_see_their_own_projects()
    {
        // Create projects for different users
        $userProject = Project::factory()->create(['user_id' => $this->user->id]);
        $otherUserProject = Project::factory()->create(['user_id' => $this->otherUser->id]);
        
        $response = $this->actingAs($this->user)->get('/projects');
        
        $response->assertStatus(200);
        
        $projects = $response->viewData('projects');
        $this->assertTrue($projects->contains($userProject));
        $this->assertFalse($projects->contains($otherUserProject));
    }

    /** @test */
    public function user_can_create_new_project()
    {
        $projectData = [
            'name' => 'Test Newsletter Project',
            'description' => 'A test project for newsletter collection',
            'allowed_origins' => "https://example.com\nhttps://test.com",
            'double_opt_in' => true,
            'welcome_email' => true,
            'admin_notifications' => false,
            'status' => 'active',
        ];
        
        $response = $this->actingAs($this->user)
                         ->post('/projects', $projectData);
        
        $response->assertRedirect('/projects');
        
        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'name' => 'Test Newsletter Project',
            'double_opt_in' => true,
            'status' => 'active',
        ]);
        
        // Check that API key and public ID are generated
        $project = Project::where('name', 'Test Newsletter Project')->first();
        $this->assertNotNull($project->api_key);
        $this->assertNotNull($project->public_id);
    }

    /** @test */
    public function project_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
                         ->post('/projects', []);
        
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function user_can_view_project_details()
    {
        // Create some subscriptions for the project
        Subscription::factory()->count(5)->create([
            'project_id' => $this->project->id,
            'status' => 'subscribed'
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$this->project->id}");
        
        $response->assertStatus(200)
                 ->assertViewIs('projects.show')
                 ->assertViewHas('project', $this->project)
                 ->assertViewHas('stats')
                 ->assertViewHas('subscriptions');
    }

    /** @test */
    public function user_cannot_view_other_users_projects()
    {
        $otherProject = Project::factory()->create([
            'user_id' => $this->otherUser->id
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$otherProject->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_update_project()
    {
        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
            'allowed_origins' => "https://newdomain.com",
            'double_opt_in' => false,
            'welcome_email' => false,
            'admin_notifications' => true,
            'status' => 'inactive',
        ];
        
        $response = $this->actingAs($this->user)
                         ->put("/projects/{$this->project->id}", $updateData);
        
        $response->assertRedirect("/projects/{$this->project->id}");
        
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'name' => 'Updated Project Name',
            'double_opt_in' => false,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function user_can_regenerate_api_key()
    {
        $originalApiKey = $this->project->api_key;
        
        $response = $this->actingAs($this->user)
                         ->post("/projects/{$this->project->id}/regenerate-api-key");
        
        $response->assertRedirect("/projects/{$this->project->id}");
        
        $this->project->refresh();
        $this->assertNotEquals($originalApiKey, $this->project->api_key);
    }

    /** @test */
    public function user_can_delete_project()
    {
        $response = $this->actingAs($this->user)
                         ->delete("/projects/{$this->project->id}");
        
        $response->assertRedirect('/projects');
        
        // Project should still exist but be deactivated
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function user_can_export_subscribers_csv()
    {
        // Create some subscriptions
        Subscription::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'status' => 'subscribed'
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$this->project->id}/export");
        
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
                 ->assertHeader('Content-Disposition');
    }

    /** @test */
    public function user_can_view_javascript_snippet()
    {
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$this->project->id}/snippet");
        
        $response->assertStatus(200)
                 ->assertViewIs('projects.snippet')
                 ->assertViewHas('project', $this->project)
                 ->assertViewHas('snippet');
    }

    /** @test */
    public function dashboard_displays_project_statistics()
    {
        // Create various subscriptions
        Subscription::factory()->count(10)->create([
            'project_id' => $this->project->id,
            'status' => 'subscribed',
            'created_at' => now()->subDays(5)
        ]);
        
        Subscription::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'created_at' => now()->subDays(1)
        ]);
        
        Subscription::factory()->count(2)->create([
            'project_id' => $this->project->id,
            'status' => 'unsubscribed',
            'created_at' => now()->subDays(10)
        ]);
        
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$this->project->id}");
        
        $response->assertStatus(200);
        
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('total_subscriptions', $stats);
        $this->assertArrayHasKey('subscribed_count', $stats);
        $this->assertArrayHasKey('recent_subscriptions', $stats);
    }

    /** @test */
    public function project_creation_form_is_accessible()
    {
        $response = $this->actingAs($this->user)
                         ->get('/projects/create');
        
        $response->assertStatus(200)
                 ->assertViewIs('projects.create');
    }

    /** @test */
    public function project_edit_form_is_accessible()
    {
        $response = $this->actingAs($this->user)
                         ->get("/projects/{$this->project->id}/edit");
        
        $response->assertStatus(200)
                 ->assertViewIs('projects.edit')
                 ->assertViewHas('project', $this->project);
    }

    /** @test */
    public function project_max_limit_is_enforced()
    {
        // Create maximum allowed projects
        $maxProjects = config('newsletter.security.max_projects_per_user', 10);
        
        Project::factory()->count($maxProjects)->create([
            'user_id' => $this->user->id
        ]);
        
        // Try to create one more
        $response = $this->actingAs($this->user)
                         ->post('/projects', [
                             'name' => 'Excess Project',
                             'status' => 'active'
                         ]);
        
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function embed_script_is_served_correctly()
    {
        $response = $this->get('/embed/newsletter.js');
        
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
                 ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    /** @test */
    public function confirmation_endpoint_works_with_signed_urls()
    {
        $subscription = Subscription::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending'
        ]);
        
        $url = $subscription->getConfirmationUrl();
        
        $response = $this->get($url);
        
        $response->assertStatus(200);
        
        $subscription->refresh();
        $this->assertEquals('subscribed', $subscription->status);
        $this->assertNotNull($subscription->confirmed_at);
    }
}
