CONFIRM YOUR SUBSCRIPTION TO {{ strtoupper($project->name) }}

Hello!

Thank you for subscribing to our newsletter. To complete your subscription and start receiving updates from {{ $project->name }}, please visit the following link:

{{ $confirmationUrl }}

Important: This confirmation link will expire in {{ config('newsletter.confirmation_expiry_hours', 48) }} hours.

If you didn't subscribe to this newsletter, you can safely ignore this email. Your email address will not be added to our mailing list.

---

This email was sent by {{ config('app.name') }}

Don't want to receive these emails? Visit: {{ $unsubscribeUrl }}