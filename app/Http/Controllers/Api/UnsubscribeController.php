<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendAdminNotification;

/**
 * @OA\Tag(
 *     name="Unsubscribe",
 *     description="Newsletter unsubscribe management"
 * )
 */
class UnsubscribeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/unsubscribe",
     *     tags={"Unsubscribe"},
     *     summary="Unsubscribe from newsletter",
     *     description="Remove email subscription from a project",
     *     @OA\Parameter(
     *         name="X-Project-ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Project public ID (ULID)"
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Project API key"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="reason", type="string", example="No longer interested")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unsubscribe successful (always returns 200 for privacy)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="message", type="string", example="If you were subscribed, you have been unsubscribed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded"
     *     )
     * )
     */
    public function store(Request $request): Response
    {
        // Get project from middleware
        $project = $request->get('project');
        
        // Validate request data
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        
        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $email = strtolower(trim($request->input('email')));
        $reason = $request->input('reason');
        
        try {
            DB::beginTransaction();
            
            // Find subscription
            $subscription = Subscription::where('project_id', $project->id)
                ->where('email', $email)
                ->first();
            
            if ($subscription && !$subscription->isUnsubscribed()) {
                // Unsubscribe the user
                $subscription->unsubscribe();
                
                // Update meta with unsubscribe information
                $meta = $subscription->meta ?? [];
                $meta['unsubscribe_reason'] = $reason;
                $meta['unsubscribed_at'] = now()->toISOString();
                $meta['unsubscribe_ip'] = $request->ip();
                $meta['unsubscribe_user_agent'] = $request->userAgent();
                
                $subscription->update(['meta' => $meta]);
                
                // Send admin notification if enabled
                if ($project->admin_notifications) {
                    SendAdminNotification::dispatch($subscription, 'unsubscribe', [
                        'reason' => $reason
                    ]);
                }
                
                // Log the unsubscribe action
                logger()->info('User unsubscribed', [
                    'project_id' => $project->id,
                    'subscription_id' => $subscription->id,
                    'email_hash' => hash('sha256', $email), // Hash for privacy
                    'reason' => $reason,
                    'ip' => $request->ip()
                ]);
            }
            
            DB::commit();
            
            // Always return success response for privacy
            // Don't leak information about whether email was subscribed
            return response([
                'success' => true,
                'message' => 'Unsubscribe request processed'
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log error for debugging
            logger()->error('Unsubscribe failed', [
                'project_id' => $project->id,
                'email_hash' => hash('sha256', $email), // Hash for privacy
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Still return success for privacy, but log the error
            return response([
                'success' => true,
                'message' => 'Unsubscribe request processed'
            ], 200);
        }
    }
    
    /**
     * Handle unsubscribe via signed URL (for email links)
     * 
     * @OA\Get(
     *     path="/unsubscribe",
     *     tags={"Unsubscribe"},
     *     summary="Unsubscribe via signed URL",
     *     description="Unsubscribe using a signed URL from email",
     *     @OA\Parameter(
     *         name="project",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Project public ID"
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Email address"
     *     ),
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="URL signature"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unsubscribe page or success message"
     *     )
     * )
     */
    public function unsubscribeViaUrl(Request $request): Response
    {
        // Validate signed URL parameters
        $validator = Validator::make($request->all(), [
            'project' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);
        
        if ($validator->fails()) {
            return response()->view('errors.invalid-unsubscribe', [], 400);
        }
        
        $projectPublicId = $request->input('project');
        $email = strtolower(trim($request->input('email')));
        
        try {
            // Find project by public ID
            $project = Project::where('public_id', $projectPublicId)->first();
            
            if (!$project || !$project->isActive()) {
                return response()->view('errors.invalid-unsubscribe', [], 404);
            }
            
            // Find and unsubscribe
            $subscription = Subscription::where('project_id', $project->id)
                ->where('email', $email)
                ->first();
            
            if ($subscription && !$subscription->isUnsubscribed()) {
                $subscription->unsubscribe();
                
                // Update meta with unsubscribe information
                $meta = $subscription->meta ?? [];
                $meta['unsubscribed_via'] = 'email_link';
                $meta['unsubscribed_at'] = now()->toISOString();
                $meta['unsubscribe_ip'] = $request->ip();
                $meta['unsubscribe_user_agent'] = $request->userAgent();
                
                $subscription->update(['meta' => $meta]);
                
                // Send admin notification if enabled
                if ($project->admin_notifications) {
                    SendAdminNotification::dispatch($subscription, 'unsubscribe', [
                        'method' => 'email_link'
                    ]);
                }
            }
            
            // Return unsubscribe confirmation page
            return response()->view('unsubscribe.success', [
                'project' => $project,
                'email' => $email
            ]);
            
        } catch (\Exception $e) {
            logger()->error('URL unsubscribe failed', [
                'project_public_id' => $projectPublicId,
                'email_hash' => hash('sha256', $email),
                'error' => $e->getMessage()
            ]);
            
            return response()->view('unsubscribe.success', [
                'project' => null,
                'email' => $email
            ]);
        }
    }
}
