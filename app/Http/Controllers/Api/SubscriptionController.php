<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendConfirmationEmail;
use App\Jobs\SendWelcomeEmail;
use App\Jobs\SendAdminNotification;

/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="Newsletter subscription management"
 * )
 */
class SubscriptionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Subscribe to newsletter",
     *     description="Add a new email subscription to a project",
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
     *             @OA\Property(property="source_url", type="string", example="https://example.com/signup"),
     *             @OA\Property(property="referrer", type="string", example="https://google.com"),
     *             @OA\Property(property="meta", type="object", example={"campaign": "summer2024"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="subscription_id", type="integer", example=123),
     *             @OA\Property(property="state", type="string", enum={"pending", "subscribed"}, example="subscribed"),
     *             @OA\Property(property="message", type="string", example="Successfully subscribed!")
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
            'source_url' => ['nullable', 'url', 'max:500'],
            'referrer' => ['nullable', 'url', 'max:500'],
            'meta' => ['nullable', 'array'],
        ]);
        
        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $email = $request->input('email');
        
        // Additional email validation using Subscription model
        $emailValidation = Subscription::validateEmail($email);
        if (!$emailValidation['valid']) {
            return response([
                'success' => false,
                'message' => implode(', ', $emailValidation['errors']),
                'errors' => ['email' => $emailValidation['errors']]
            ], 422);
        }
        
        $normalizedEmail = $emailValidation['email'];
        
        try {
            DB::beginTransaction();
            
            // Check if subscription already exists
            $existingSubscription = Subscription::where('project_id', $project->id)
                ->where('email', $normalizedEmail)
                ->first();
            
            if ($existingSubscription) {
                // Handle existing subscription based on current status
                $response = $this->handleExistingSubscription($existingSubscription, $request, $project);
                DB::commit();
                return $response;
            }
            
            // Create new subscription
            $subscription = $this->createNewSubscription($project, $normalizedEmail, $request);
            
            // Queue emails based on project settings
            $this->queueEmails($subscription, $project);
            
            DB::commit();
            
            return response([
                'success' => true,
                'data' => [
                    'subscription_id' => $subscription->id,
                    'email' => $subscription->email,
                    'status' => $subscription->status,
                    'requires_confirmation' => $subscription->status === Subscription::STATUS_PENDING,
                ],
                'message' => $this->getSuccessMessage($subscription->status)
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log error for debugging
            logger()->error('Subscription creation failed', [
                'project_id' => $project->id,
                'email' => $normalizedEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Handle existing subscription based on current status
     */
    private function handleExistingSubscription(Subscription $subscription, Request $request, Project $project): Response
    {
        switch ($subscription->status) {
            case Subscription::STATUS_UNSUBSCRIBED:
                // Resubscribe user
                $subscription->subscribe();
                
                // Update tracking information
                $subscription->update([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $request->input('referrer') ?: $request->header('Referer'),
                    'source_url' => $request->input('source_url'),
                    'meta' => $request->input('meta'),
                ]);
                
                // Queue welcome email if enabled
                if ($project->welcome_email) {
                    SendWelcomeEmail::dispatch($subscription);
                }
                
                return response([
                    'success' => true,
                    'data' => [
                        'subscription_id' => $subscription->id,
                        'email' => $subscription->email,
                        'status' => $subscription->status,
                        'requires_confirmation' => false,
                    ],
                    'message' => 'You have been resubscribed successfully!'
                ], 200);
                
            case Subscription::STATUS_PENDING:
            case Subscription::STATUS_SUBSCRIBED:
                // Idempotent response for already subscribed users
                return response([
                    'success' => true,
                    'data' => [
                        'subscription_id' => $subscription->id,
                        'email' => $subscription->email,
                        'status' => $subscription->status,
                        'requires_confirmation' => $subscription->status === Subscription::STATUS_PENDING,
                    ],
                    'message' => $subscription->status === Subscription::STATUS_PENDING 
                        ? 'Please check your email to confirm your subscription.'
                        : 'You are already subscribed!'
                ], 200);
                
            case Subscription::STATUS_BOUNCED:
                return response([
                    'success' => false,
                    'message' => 'This email address has been marked as undeliverable.',
                    'errors' => ['email' => ['This email address has been marked as undeliverable.']]
                ], 422);
                
            default:
                return response([
                    'success' => false,
                    'message' => 'Unable to process subscription.'
                ], 422);
        }
    }
    
    /**
     * Create new subscription
     */
    private function createNewSubscription(Project $project, string $email, Request $request): Subscription
    {
        $status = $project->double_opt_in 
            ? Subscription::STATUS_PENDING 
            : Subscription::STATUS_SUBSCRIBED;
            
        return Subscription::create([
            'project_id' => $project->id,
            'email' => $email,
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->input('referrer') ?: $request->header('Referer'),
            'source_url' => $request->input('source_url'),
            'meta' => $request->input('meta'),
            'confirmed_at' => $status === Subscription::STATUS_SUBSCRIBED ? now() : null,
        ]);
    }
    
    /**
     * Queue appropriate emails
     */
    private function queueEmails(Subscription $subscription, Project $project): void
    {
        if ($subscription->status === Subscription::STATUS_PENDING) {
            // Send confirmation email for double opt-in
            SendConfirmationEmail::dispatch($subscription);
        } else {
            // Send welcome email if enabled
            if ($project->welcome_email) {
                SendWelcomeEmail::dispatch($subscription);
            }
        }
        
        // Send admin notification if enabled
        if ($project->admin_notifications) {
            SendAdminNotification::dispatch($subscription, 'new_subscription');
        }
    }
    
    /**
     * Get success message based on subscription status
     */
    private function getSuccessMessage(string $status): string
    {
        switch ($status) {
            case Subscription::STATUS_PENDING:
                return 'Please check your email to confirm your subscription.';
            case Subscription::STATUS_SUBSCRIBED:
                return 'You are successfully subscribed! 🎉';
            default:
                return 'Subscription processed successfully.';
        }
    }
}
