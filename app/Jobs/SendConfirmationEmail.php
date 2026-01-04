<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Mail\ConfirmationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendConfirmationEmail implements ShouldQueue
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
            // Check if subscription still exists and is in pending status
            if (!$this->subscription->exists || $this->subscription->status !== Subscription::STATUS_PENDING) {
                logger()->info('Skipping confirmation email - subscription no longer pending', [
                    'subscription_id' => $this->subscription->id,
                    'status' => $this->subscription->status ?? 'deleted'
                ]);
                return;
            }
            
            // Check if project is still active
            if (!$this->subscription->project || !$this->subscription->project->isActive()) {
                logger()->warning('Skipping confirmation email - project inactive', [
                    'subscription_id' => $this->subscription->id,
                    'project_id' => $this->subscription->project_id
                ]);
                return;
            }
            
            // Send the confirmation email
            Mail::to($this->subscription->email)
                ->send(new ConfirmationEmail($this->subscription));
            
            // Log successful send
            logger()->info('Confirmation email sent successfully', [
                'subscription_id' => $this->subscription->id,
                'project_id' => $this->subscription->project_id,
                'email_hash' => hash('sha256', $this->subscription->email)
            ]);
            
            // Update metadata to track email send
            $meta = $this->subscription->meta ?? [];
            $meta['confirmation_email_sent_at'] = now()->toISOString();
            $meta['confirmation_email_attempts'] = ($meta['confirmation_email_attempts'] ?? 0) + 1;
            $this->subscription->update(['meta' => $meta]);
            
        } catch (\Exception $e) {
            logger()->error('Failed to send confirmation email', [
                'subscription_id' => $this->subscription->id,
                'project_id' => $this->subscription->project_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts() ?? 1
            ]);
            
            throw $e;
        }
    }
}
