<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Newsletter Subscription - {{ $project->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 24px;
        }
        .notification-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .subscription-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #bbdefb;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            margin: 0;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin: 5px 0 0 0;
        }
        .footer {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
        .email-hash {
            font-family: monospace;
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📧 New Newsletter Subscription</h1>
        </div>

        <div class="notification-badge">
            <h2 style="margin: 0;">🎉 New subscriber joined!</h2>
            <p style="margin: 5px 0 0 0;">{{ $project->name }} newsletter community is growing!</p>
        </div>

        <div class="subscription-details">
            <h3>Subscription Details</h3>
            <ul>
                <li><strong>Project:</strong> {{ $project->name }}</li>
                <li><strong>Subscriber Email:</strong> <span class="email-hash">{{ substr(hash('sha256', $subscriberEmail), 0, 16) }}...</span></li>
                <li><strong>Subscription Date:</strong> {{ $subscriptionDate->format('F j, Y \a\t g:i A T') }}</li>
                <li><strong>Project ID:</strong> {{ $project->public_id }}</li>
                <li><strong>Subscription Status:</strong> ✅ Confirmed</li>
            </ul>
        </div>

        <h3>Project Statistics</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-number">{{ $projectStats['total_subscriptions'] }}</p>
                <p class="stat-label">Total Subscribers</p>
            </div>
            <div class="stat-card">
                <p class="stat-number">{{ $projectStats['recent_subscriptions'] }}</p>
                <p class="stat-label">New This Week</p>
            </div>
        </div>

        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #856404;">📊 Admin Dashboard</h4>
            <p style="margin: 0; color: #856404;">
                View detailed analytics and manage your newsletter projects in the admin dashboard.
            </p>
        </div>

        <div class="footer">
            <p>This notification was sent by {{ config('app.name') }}</p>
            <p><small>Email addresses are hashed for security in notifications</small></p>
        </div>
    </div>
</body>
</html>