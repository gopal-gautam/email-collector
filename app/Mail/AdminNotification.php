<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Subscription $subscription;

    /**
     * Create a new message instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Newsletter Subscription - {$this->subscription->project->name}",
            from: config('mail.from.address'),
            replyTo: config('newsletter.reply_to_email', config('mail.from.address'))
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.admin-notification',
            text: 'emails.admin-notification-text',
            with: [
                'subscription' => $this->subscription,
                'project' => $this->subscription->project,
                'subscriberEmail' => $this->subscription->email,
                'subscriptionDate' => $this->subscription->created_at,
                'projectStats' => [
                    'total_subscriptions' => $this->subscription->project->subscriptions()->subscribed()->count(),
                    'recent_subscriptions' => $this->subscription->project->subscriptions()
                        ->subscribed()
                        ->where('created_at', '>=', now()->subDays(7))
                        ->count(),
                ]
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}