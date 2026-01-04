NEW NEWSLETTER SUBSCRIPTION - {{ strtoupper($project->name) }}

📧 New subscriber joined!

{{ $project->name }} newsletter community is growing!

SUBSCRIPTION DETAILS:
- Project: {{ $project->name }}
- Subscriber Email: {{ substr(hash('sha256', $subscriberEmail), 0, 16) }}... (hashed for security)
- Subscription Date: {{ $subscriptionDate->format('F j, Y \a\t g:i A T') }}
- Project ID: {{ $project->public_id }}
- Subscription Status: ✅ Confirmed

PROJECT STATISTICS:
- Total Subscribers: {{ $projectStats['total_subscriptions'] }}
- New This Week: {{ $projectStats['recent_subscriptions'] }}

📊 ADMIN DASHBOARD
View detailed analytics and manage your newsletter projects in the admin dashboard.

---

This notification was sent by {{ config('app.name') }}

Email addresses are hashed for security in notifications