WELCOME TO {{ strtoupper($project->name) }} NEWSLETTER!

🎉 Welcome aboard!

Hello!

We're thrilled to have you as part of the {{ $project->name }} newsletter community! Your subscription has been confirmed and you're all set to receive our latest updates, insights, and exclusive content.

WHAT TO EXPECT:
- Regular updates and insights from {{ $project->name }}
- Exclusive content and announcements  
- Tips, resources, and valuable information
- No spam - we respect your inbox!

We're committed to providing you with valuable content and we promise to respect your privacy. You can unsubscribe at any time using the link below.

Thank you for trusting us with your inbox. We look forward to sharing great content with you!

Best regards,
The {{ $project->name }} Team

---

This email was sent by {{ config('app.name') }}

Unsubscribe from this newsletter: {{ $unsubscribeUrl }}

Subscription confirmed on {{ $subscription->created_at->format('F j, Y \a\t g:i A') }}