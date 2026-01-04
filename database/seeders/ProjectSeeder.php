<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\ApiRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Symfony\Component\Uid\Ulid;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user if one doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create demo projects
        $projects = [
            [
                'name' => 'Tech Blog Newsletter',
                'description' => 'Weekly newsletter about the latest in technology and programming.',
                'allowed_origins' => ['https://techblog.example.com', 'https://www.techblog.example.com'],
                'double_opt_in' => true,
                'welcome_email' => true,
                'admin_notifications' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Product Updates',
                'description' => 'Get notified about new features and product announcements.',
                'allowed_origins' => ['https://myapp.example.com'],
                'double_opt_in' => false,
                'welcome_email' => false,
                'admin_notifications' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Marketing Campaign',
                'description' => 'Promotional newsletter for special offers and deals.',
                'allowed_origins' => ['*'],
                'double_opt_in' => true,
                'welcome_email' => true,
                'admin_notifications' => true,
                'status' => 'inactive',
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create([
                'user_id' => $user->id,
                'public_id' => (string) new Ulid(),
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'allowed_origins' => json_encode($projectData['allowed_origins']),
                'api_key' => 'nlc_' . \Illuminate\Support\Str::random(56),
                'double_opt_in' => $projectData['double_opt_in'],
                'welcome_email' => $projectData['welcome_email'],
                'admin_notifications' => $projectData['admin_notifications'],
                'status' => $projectData['status'],
            ]);

            // Create demo subscriptions for active projects
            if ($project->status === 'active') {
                $this->createDemoSubscriptions($project);
                $this->createDemoApiRequests($project);
            }
        }

        $this->command->info('Created demo projects and subscriptions.');
    }

    /**
     * Create demo subscriptions for a project
     */
    private function createDemoSubscriptions(Project $project): void
    {
        $emails = [
            'john.doe@example.com',
            'jane.smith@example.com',
            'bob.johnson@example.com',
            'alice.wilson@example.com',
            'charlie.brown@example.com',
            'diana.prince@example.com',
            'eve.adams@example.com',
            'frank.miller@example.com',
            'grace.kelly@example.com',
            'henry.ford@example.com',
        ];

        $statuses = ['subscribed', 'subscribed', 'subscribed', 'subscribed', 'pending', 'unsubscribed'];

        foreach ($emails as $index => $email) {
            $status = $statuses[array_rand($statuses)];
            $createdAt = now()->subDays(rand(1, 30));
            
            Subscription::create([
                'project_id' => $project->id,
                'email' => $email,
                'status' => $status,
                'ip_address' => $this->generateRandomIp(),
                'user_agent' => $this->generateRandomUserAgent(),
                'source_url' => 'https://example.com/newsletter',
                'confirmed_at' => $status === 'subscribed' ? $createdAt->addMinutes(rand(1, 60)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * Create demo API requests for analytics
     */
    private function createDemoApiRequests(Project $project): void
    {
        $paths = ['/api/v1/subscriptions', '/api/v1/unsubscribe'];
        $statuses = [200, 200, 200, 200, 200, 422, 429, 500];
        
        // Create requests for the last 30 days
        for ($i = 0; $i < 150; $i++) {
            $date = now()->subDays(rand(0, 30));
            
            ApiRequest::create([
                'project_id' => $project->id,
                'path' => $paths[array_rand($paths)],
                'method' => 'POST',
                'ip_address' => $this->generateRandomIp(),
                'user_agent' => $this->generateRandomUserAgent(),
                'status_code' => $statuses[array_rand($statuses)],
                'response_time' => rand(50, 500),
                'created_at' => $date,
            ]);
        }
    }

    /**
     * Generate a random IP address for demo data
     */
    private function generateRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    /**
     * Generate a random user agent for demo data
     */
    private function generateRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];
        
        return $userAgents[array_rand($userAgents)];
    }
}
