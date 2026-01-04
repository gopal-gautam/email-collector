<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ConfirmationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Subscription $subscription;
    public string $confirmationUrl;
    public string $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->confirmationUrl = $subscription->generateConfirmationUrl();
        $this->unsubscribeUrl = $subscription->generateUnsubscribeUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'hello@example.com'),
                config('mail.from.name', config('app.name'))
            ),
            subject: 'Please confirm your subscription to ' . $this->subscription->project->name,
            tags: ['confirmation', 'newsletter'],
            metadata: [
                'project_id' => $this->subscription->project_id,
                'subscription_id' => $this->subscription->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation',
            text: 'emails.confirmation-text',
            with: [
                'subscription' => $this->subscription,
                'project' => $this->subscription->project,
                'confirmationUrl' => $this->confirmationUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
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
