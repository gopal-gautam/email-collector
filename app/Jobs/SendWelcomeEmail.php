<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Mail\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public Subscription $subscription;
    
    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function handle(): void
    {
        try {
            if (!$this->subscription->isSubscribed() || !$this->subscription->project->isActive()) {
                return;
            }
            
            Mail::to($this->subscription->email)
                ->send(new WelcomeEmail($this->subscription));
            
            logger()->info('Welcome email sent', [
                'subscription_id' => $this->subscription->id,
                'email_hash' => hash('sha256', $this->subscription->email)
            ]);
            
        } catch (\Exception $e) {
            logger()->error('Failed to send welcome email', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
