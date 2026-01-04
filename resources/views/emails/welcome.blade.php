<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $project->name }}!</title>
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
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .welcome-message {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .welcome-message h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .footer {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
        .unsubscribe-link {
            color: #6c757d;
            text-decoration: underline;
        }
        .project-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $project->name }}</h1>
        </div>

        <div class="content">
            <div class="welcome-message">
                <h2>🎉 Welcome aboard!</h2>
                <p>Thank you for joining our newsletter community!</p>
            </div>

            <p>Hello!</p>

            <p>We're thrilled to have you as part of the <strong>{{ $project->name }}</strong> newsletter community! Your subscription has been confirmed and you're all set to receive our latest updates, insights, and exclusive content.</p>

            <div class="project-info">
                <h3>What to expect:</h3>
                <ul>
                    <li>Regular updates and insights from {{ $project->name }}</li>
                    <li>Exclusive content and announcements</li>
                    <li>Tips, resources, and valuable information</li>
                    <li>No spam - we respect your inbox!</li>
                </ul>
            </div>

            <p>We're committed to providing you with valuable content and we promise to respect your privacy. You can unsubscribe at any time using the link below.</p>

            <p>Thank you for trusting us with your inbox. We look forward to sharing great content with you!</p>

            <p>Best regards,<br>
            <strong>The {{ $project->name }} Team</strong></p>
        </div>

        <div class="footer">
            <p>This email was sent by {{ config('app.name') }}</p>
            <p>
                <a href="{{ $unsubscribeUrl }}" class="unsubscribe-link">
                    Unsubscribe from this newsletter
                </a>
            </p>
            <p>
                <small>
                    Subscription confirmed on {{ $subscription->created_at->format('F j, Y \a\t g:i A') }}
                </small>
            </p>
        </div>
    </div>
</body>
</html>