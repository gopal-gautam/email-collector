<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Mail\AdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAdminNotification implements ShouldQueue
{
    use Queueable;

    public Subscription $subscription;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Only send admin notification if configured and project is active
            if (!$this->subscription->project->isActive()) {
                return;
            }
            
            $adminEmail = config('newsletter.admin_notification_email');
            
            if (!$adminEmail) {
                logger()->info('Admin notification email not configured, skipping notification');
                return;
            }
            
            Mail::to($adminEmail)
                ->send(new AdminNotification($this->subscription));
            
            logger()->info('Admin notification sent', [
                'subscription_id' => $this->subscription->id,
                'project_id' => $this->subscription->project->id,
                'email_hash' => hash('sha256', $this->subscription->email)
            ]);
            
        } catch (\Exception $e) {
            logger()->error('Failed to send admin notification', [
                'subscription_id' => $this->subscription->id,
                'project_id' => $this->subscription->project->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
