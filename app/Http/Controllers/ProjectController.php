<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subscription;
use App\Models\ApiRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function __construct()
    {
        // Middleware is now handled in routes/web.php
        // No need to define middleware in controller constructor for Laravel 11
    }
    
    /**
     * Display a listing of the user's projects
     */
    public function index()
    {
        $user = Auth::user();
        
        $projects = Project::where('user_id', $user->id)
            ->withCount([
                'subscriptions',
                'subscriptions as subscribed_count' => function($query) {
                    $query->where('status', Subscription::STATUS_SUBSCRIBED);
                },
                'subscriptions as pending_count' => function($query) {
                    $query->where('status', Subscription::STATUS_PENDING);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
            
        return view('projects.index', compact('projects'));
    }
    
    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        return view('projects.create');
    }
    
    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check project limit
        $maxProjects = config('newsletter.security.max_projects_per_user', 10);
        if ($user->projects()->count() >= $maxProjects) {
            return back()->withErrors([
                'name' => "You can only create up to {$maxProjects} projects."
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'allowed_origins' => ['nullable', 'string'],
            'double_opt_in' => ['boolean'],
            'welcome_email' => ['boolean'],
            'admin_notifications' => ['boolean'],
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Parse allowed origins
            $allowedOrigins = $this->parseAllowedOrigins($request->input('allowed_origins'));
            
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'allowed_origins' => $allowedOrigins,
                'double_opt_in' => $request->boolean('double_opt_in'),
                'welcome_email' => $request->boolean('welcome_email'),
                'admin_notifications' => $request->boolean('admin_notifications'),
            ]);
            
            DB::commit();
            
            return redirect()->route('projects.index')
                ->with('success', 'Project created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            logger()->error('Project creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'name' => 'Failed to create project. Please try again.'
            ])->withInput();
        }
    }
    
    /**
     * Display the specified project with analytics
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        
        // Get analytics data
        $stats = $this->getProjectAnalytics($project);
        
        // Get recent subscriptions
        $subscriptions = $project->subscriptions()
            ->with([])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('projects.show', compact('project', 'stats', 'subscriptions'));
    }
    
    /**
     * Show the form for editing the project
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        
        return view('projects.edit', compact('project'));
    }
    
    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'allowed_origins' => ['nullable', 'string'],
            'double_opt_in' => ['boolean'],
            'welcome_email' => ['boolean'],
            'admin_notifications' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Parse allowed origins
            $allowedOrigins = $this->parseAllowedOrigins($request->input('allowed_origins'));
            
            $project->update([
                'name' => $request->input('name'),
                'allowed_origins' => $allowedOrigins,
                'double_opt_in' => $request->boolean('double_opt_in'),
                'welcome_email' => $request->boolean('welcome_email'),
                'admin_notifications' => $request->boolean('admin_notifications'),
                'status' => $request->input('status'),
            ]);
            
            return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
            
        } catch (\Exception $e) {
            logger()->error('Project update failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'name' => 'Failed to update project. Please try again.'
            ])->withInput();
        }
    }
    
    /**
     * Remove the specified project
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        try {
            DB::beginTransaction();
            
            // Soft delete by setting status to inactive
            $project->update(['status' => 'inactive']);
            
            DB::commit();
            
            return redirect()->route('projects.index')
                ->with('success', 'Project deactivated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            logger()->error('Project deletion failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to deactivate project. Please try again.'
            ]);
        }
    }
    
    /**
     * Regenerate project API key
     */
    public function regenerateApiKey(Project $project)
    {
        $this->authorize('update', $project);
        
        try {
            $project->api_key = Str::random(64);
            $project->save();
            
            return redirect()->route('projects.show', $project)->with('success', 'API key regenerated successfully!');
            
        } catch (\Exception $e) {
            logger()->error('API key regeneration failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to regenerate API key. Please try again.'
            ]);
        }
    }
    
    /**
     * Export subscriptions as CSV
     */
    public function export(Project $project)
    {
        $this->authorize('view', $project);
        
        try {
            $subscriptions = $project->subscriptions()
                ->orderBy('created_at', 'desc')
                ->get();
            
            $filename = 'subscriptions-' . $project->name . '-' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($subscriptions) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, ['Email', 'Status', 'Created At', 'Confirmed At', 'Source URL', 'Referrer']);
                
                // CSV data
                foreach ($subscriptions as $subscription) {
                    fputcsv($file, [
                        $subscription->email,
                        $subscription->status,
                        $subscription->created_at->toDateTimeString(),
                        $subscription->confirmed_at ? $subscription->confirmed_at->toDateTimeString() : '',
                        $subscription->source_url ?? '',
                        $subscription->referrer ?? '',
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            logger()->error('CSV export failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to export data. Please try again.'
            ]);
        }
    }
    
    /**
     * Display project analytics
     */
    public function analytics(Project $project)
    {
        $this->authorize('view', $project);
        
        $analytics = $this->getProjectAnalytics($project);
        
        return view('projects.analytics', compact('project', 'analytics'));
    }
    
    /**
     * Display project subscriptions
     */
    public function subscriptions(Project $project)
    {
        $this->authorize('view', $project);
        
        $subscriptions = $project->subscriptions()
            ->orderBy('created_at', 'desc')
            ->paginate(50);
            
        return view('projects.subscriptions', compact('project', 'subscriptions'));
    }
    
    /**
     * Get JavaScript snippet for the project
     */
    public function snippet(Project $project)
    {
        $this->authorize('view', $project);
        
        // Generate JavaScript snippet
        $snippet = $this->generateJavaScriptSnippet($project);
        
        return view('projects.snippet', compact('project', 'snippet'));
    }
    
    /**
     * Parse allowed origins from textarea input
     */
    private function parseAllowedOrigins(?string $origins): array
    {
        if (empty($origins)) {
            return [];
        }
        
        $lines = explode("\n", $origins);
        $parsed = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $parsed[] = $line;
            }
        }
        
        return $parsed;
    }
    
    /**
     * Get analytics data for the project
     */
    private function getProjectAnalytics(Project $project, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        // Basic subscription stats
        $totalSubscriptions = $project->subscriptions()->count();
        $subscribedCount = $project->subscriptions()->subscribed()->count();
        $pendingCount = $project->subscriptions()->pending()->count();
        $unsubscribedCount = $project->subscriptions()->unsubscribed()->count();
        
        // Recent subscriptions
        $recentSubscriptions = $project->subscriptions()
            ->where('created_at', '>=', $startDate)
            ->count();
            
        // API requests analytics (if ApiRequest model has this method)
        $apiAnalytics = [];
        if (method_exists(ApiRequest::class, 'getAnalyticsForProject')) {
            $apiAnalytics = ApiRequest::getAnalyticsForProject($project->id, $days);
        }
        
        // Daily subscription stats for chart
        $dailyStats = $project->subscriptions()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();
            
        // Fill missing dates
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => $date,
                'subscriptions' => $dailyStats[$date]['count'] ?? 0
            ];
        }
        
        return [
            'total_subscriptions' => $totalSubscriptions,
            'subscribed_count' => $subscribedCount,
            'pending_count' => $pendingCount,
            'unsubscribed_count' => $unsubscribedCount,
            'recent_subscriptions' => $recentSubscriptions,
            'conversion_rate' => $totalSubscriptions > 0 ? round(($subscribedCount / $totalSubscriptions) * 100, 2) : 0,
            'chart_data' => $chartData,
            'api_analytics' => $apiAnalytics,
        ];
    }
    
    /**
     * Generate JavaScript snippet for the project
     */
    private function generateJavaScriptSnippet(Project $project): string
    {
        $baseUrl = url('/');
        $projectId = $project->public_id; // Use public_id instead of internal id
        $apiKey = $project->api_key;
        
        return "<!--- Add this HTML where you want the newsletter form to appear --->
<div id=\"newsletter-signup\" 
     data-project-id=\"{$projectId}\"
     data-api-key=\"{$apiKey}\"
     data-button-text=\"Subscribe\"
     data-placeholder=\"Enter your email address\"
     data-success-message=\"Thank you for subscribing!\">
</div>

<!--- Add this script tag before the closing </body> tag --->
<script src=\"{$baseUrl}/embed/newsletter.js\"></script>
";
    }
}
