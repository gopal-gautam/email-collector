<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        // Check origin for regular requests with Origin header
        $project = $request->get('project');
        $origin = $request->header('Origin');
        
        if ($origin && !$this->isOriginAllowed($project, $origin)) {
            return response()->json([
                'success' => false,
                'message' => 'CORS policy violation: Origin not allowed'
            ], 403);
        }

        // Process the actual request
        $response = $next($request);

        // Add CORS headers to response
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Handle preflight OPTIONS request
     */
    private function handlePreflightRequest(Request $request): Response
    {
        $project = $request->get('project');
        $origin = $request->header('Origin');

        // Check if origin is allowed
        if (!$this->isOriginAllowed($project, $origin)) {
            return response('', 403);
        }

        $response = response('', 204);
        
        // Add preflight headers - use proper origin or * based on project settings
        $allowedOrigins = $project && $project->allowed_origins ? $project->allowed_origins : [];
        if (is_string($allowedOrigins)) {
            $allowedOrigins = json_decode($allowedOrigins, true) ?: [];
        }
        
        if (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 
            'Content-Type, Accept, Authorization, X-Requested-With, X-Project-ID, X-Api-Key, Origin'
        );
        $response->headers->set('Access-Control-Allow-Credentials', 'false');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
        $response->headers->set('Vary', 'Origin');

        return $response;
    }

    /**
     * Add CORS headers to response
     */
    private function addCorsHeaders(Request $request, Response $response): Response
    {
        $project = $request->get('project');
        $origin = $request->header('Origin');

        // Always add Vary: Origin header
        $response->headers->set('Vary', 'Origin', false);

        // Check if origin is allowed
        if ($this->isOriginAllowed($project, $origin)) {
            // If project allows all origins with *, return * for backwards compatibility
            // Otherwise return the specific origin that was validated
            $allowedOrigins = $project && $project->allowed_origins ? $project->allowed_origins : [];
            if (is_string($allowedOrigins)) {
                $allowedOrigins = json_decode($allowedOrigins, true) ?: [];
            }
            
            if (in_array('*', $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }
            
            $response->headers->set('Access-Control-Allow-Credentials', 'false');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 
                'Content-Type, Accept, Authorization, X-Requested-With, X-Project-ID, X-Api-Key, Origin'
            );
            
            // Expose specific headers if needed
            $response->headers->set('Access-Control-Expose-Headers', 
                'Content-Length, Content-Type, X-Subscription-ID'
            );
        }

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Check if the origin is allowed for the project
     */
    private function isOriginAllowed($project, ?string $origin): bool
    {
        // If no project (shouldn't happen with middleware order), deny
        if (!$project) {
            return false;
        }

        // If no origin header, allow (for non-browser requests)
        if (!$origin) {
            return true;
        }

        // Normalize origin to lowercase for case-insensitive comparison
        $normalizedOrigin = strtolower($origin);
        
        // Use project's origin validation method with normalized origin
        return $project->isOriginAllowed($normalizedOrigin);
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none';",
        ];

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value, false);
        }
    }
}
