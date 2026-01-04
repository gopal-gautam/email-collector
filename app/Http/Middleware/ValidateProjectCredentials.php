<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Project;
use App\Models\ApiRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ValidateProjectCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): HttpResponse
    {
        $startTime = microtime(true);
        
        // Allow OPTIONS requests to pass through for CORS preflight
        if ($request->isMethod('OPTIONS')) {
            // For OPTIONS requests, try to get project but don't fail if not found
            $projectId = $request->header('X-Project-ID');
            $apiKey = $request->header('X-Api-Key');
            
            if ($projectId && $apiKey) {
                $project = Project::where('public_id', $projectId)
                                 ->where('api_key', $apiKey)
                                 ->first();
                
                if ($project && $project->isActive()) {
                    $request->attributes->set('project', $project);
                    $request->merge(['project' => $project]);
                }
            }
            
            return $next($request);
        }
        
        // Get credentials from headers
        $projectId = $request->header('X-Project-ID');
        $apiKey = $request->header('X-Api-Key');
        
        // Check if both headers are present
        if (!$projectId || !$apiKey) {
            $this->logRequest($request, null, 401, $startTime);
            return response()->json([
                'error' => 'Missing required headers',
                'message' => 'Both X-Project-ID and X-Api-Key headers are required'
            ], 401);
        }
        
        // Find project by public_id and api_key
        $project = Project::where('public_id', $projectId)
                         ->where('api_key', $apiKey)
                         ->first();
        
        if (!$project) {
            $this->logRequest($request, null, 401, $startTime);
            return response()->json([
                'error' => 'Invalid credentials',
                'message' => 'Project ID or API key is invalid'
            ], 401);
        }
        
        // Check if project is active
        if (!$project->isActive()) {
            $this->logRequest($request, $project->id, 403, $startTime);
            return response()->json([
                'error' => 'Project suspended',
                'message' => 'This project has been suspended'
            ], 403);
        }
        
        // Add project to request for use in controllers
        $request->attributes->set('project', $project);
        $request->merge(['project' => $project]);
        
        // Process the request
        $response = $next($request);
        
        // Log the request after processing
        $this->logRequest($request, $project->id, $response->getStatusCode(), $startTime);
        
        return $response;
    }
    
    /**
     * Log API request for analytics and rate limiting
     */
    private function logRequest(Request $request, ?int $projectId, int $statusCode, float $startTime): void
    {
        try {
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            
            ApiRequest::logRequest(
                $projectId,
                $request->ip(),
                $request->userAgent(),
                '/' . $request->path(),
                $request->method(),
                $statusCode,
                $responseTime,
                $this->getRelevantHeaders($request)
            );
        } catch (\Exception $e) {
            // Don't let logging errors affect the API response
            logger()->error('Failed to log API request: ' . $e->getMessage());
        }
    }
    
    /**
     * Get relevant headers for logging (excluding sensitive data)
     */
    private function getRelevantHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        
        // Remove sensitive headers
        unset($headers['x-api-key']);
        unset($headers['authorization']);
        unset($headers['cookie']);
        
        // Keep only relevant headers
        $relevantHeaders = [];
        $allowedHeaders = [
            'content-type',
            'accept',
            'origin',
            'referer',
            'user-agent',
            'x-forwarded-for',
            'x-real-ip',
        ];
        
        foreach ($allowedHeaders as $header) {
            if (isset($headers[$header])) {
                $relevantHeaders[$header] = $headers[$header];
            }
        }
        
        return $relevantHeaders;
    }
}
