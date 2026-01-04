<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWelcomeEmail;
use App\Jobs\SendAdminNotification;

class ConfirmSubscriptionController extends Controller
{
    /**
     * Handle subscription confirmation via signed URL
     */
    public function __invoke(Request $request): Response
    {
        // Get subscription ID from signed URL
        $subscriptionId = $request->input('subscription');
        
        if (!$subscriptionId) {
            return response()->view('errors.invalid-confirmation', [
                'message' => 'Invalid confirmation link.'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Find the subscription
            $subscription = Subscription::with('project')->find($subscriptionId);
            
            if (!$subscription) {
                DB::rollback();
                return response()->view('errors.invalid-confirmation', [
                    'message' => 'Subscription not found.'
                ], 404);
            }
            
            // Check if project is still active
            if (!$subscription->project || !$subscription->project->isActive()) {
                DB::rollback();
                return response()->view('errors.invalid-confirmation', [
                    'message' => 'This project is no longer active.'
                ], 404);
            }
            
            // Handle confirmation based on current status
            $result = $this->handleConfirmation($subscription, $request);
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            logger()->error('Subscription confirmation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('errors.confirmation-error', [
                'message' => 'An error occurred while confirming your subscription.'
            ], 500);
        }
    }
    
    /**
     * Handle the confirmation logic
     */
    private function handleConfirmation(Subscription $subscription, Request $request): Response
    {
        switch ($subscription->status) {
            case Subscription::STATUS_PENDING:
                // Confirm the subscription
                $subscription->subscribe();
                
                // Update confirmation metadata
                $meta = $subscription->meta ?? [];
                $meta['confirmed_at'] = now()->toISOString();
                $meta['confirmation_ip'] = $request->ip();
                $meta['confirmation_user_agent'] = $request->userAgent();
                $subscription->update(['meta' => $meta]);
                
                // Queue welcome email if enabled
                if ($subscription->project->welcome_email) {
                    SendWelcomeEmail::dispatch($subscription);
                }
                
                // Send admin notification if enabled
                if ($subscription->project->admin_notifications) {
                    SendAdminNotification::dispatch($subscription, 'subscription_confirmed');
                }
                
                logger()->info('Subscription confirmed', [
                    'subscription_id' => $subscription->id,
                    'project_id' => $subscription->project_id,
                    'email_hash' => hash('sha256', $subscription->email)
                ]);
                
                return response()->view('confirmation.success', [
                    'subscription' => $subscription,
                    'project' => $subscription->project,
                    'message' => 'Your subscription has been confirmed successfully!'
                ]);
                
            case Subscription::STATUS_SUBSCRIBED:
                // Already confirmed
                return response()->view('confirmation.already-confirmed', [
                    'subscription' => $subscription,
                    'project' => $subscription->project,
                    'message' => 'Your subscription was already confirmed.'
                ]);
                
            case Subscription::STATUS_UNSUBSCRIBED:
                // User had unsubscribed, offer to resubscribe
                return response()->view('confirmation.resubscribe', [
                    'subscription' => $subscription,
                    'project' => $subscription->project,
                    'message' => 'You had previously unsubscribed. Would you like to resubscribe?'
                ]);
                
            case Subscription::STATUS_BOUNCED:
                return response()->view('confirmation.bounced', [
                    'subscription' => $subscription,
                    'project' => $subscription->project,
                    'message' => 'This email address has been marked as undeliverable.'
                ]);
                
            default:
                return response()->view('errors.invalid-confirmation', [
                    'message' => 'Invalid subscription status.'
                ], 400);
        }
    }
    
    /**
     * Handle resubscription request
     */
    public function resubscribe(Request $request, Subscription $subscription): Response
    {
        try {
            // Validate that this is a legitimate resubscribe request
            if ($subscription->status !== Subscription::STATUS_UNSUBSCRIBED) {
                return response()->view('errors.invalid-confirmation', [
                    'message' => 'Invalid resubscribe request.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Resubscribe the user
            $subscription->subscribe();
            
            // Update metadata
            $meta = $subscription->meta ?? [];
            $meta['resubscribed_at'] = now()->toISOString();
            $meta['resubscribe_ip'] = $request->ip();
            $meta['resubscribe_user_agent'] = $request->userAgent();
            $subscription->update(['meta' => $meta]);
            
            // Queue welcome email if enabled
            if ($subscription->project->welcome_email) {
                SendWelcomeEmail::dispatch($subscription);
            }
            
            // Send admin notification if enabled
            if ($subscription->project->admin_notifications) {
                SendAdminNotification::dispatch($subscription, 'resubscribed');
            }
            
            DB::commit();
            
            logger()->info('User resubscribed', [
                'subscription_id' => $subscription->id,
                'project_id' => $subscription->project_id,
                'email_hash' => hash('sha256', $subscription->email)
            ]);
            
            return response()->view('confirmation.resubscribe-success', [
                'subscription' => $subscription,
                'project' => $subscription->project,
                'message' => 'You have been resubscribed successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            logger()->error('Resubscription failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->view('errors.confirmation-error', [
                'message' => 'An error occurred while resubscribing.'
            ], 500);
        }
    }
}
