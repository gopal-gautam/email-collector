<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Confirm Your Subscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .unsubscribe {
            font-size: 12px;
            color: #6c757d;
            margin-top: 20px;
        }
        .unsubscribe a {
            color: #6c757d;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class=\"header\">
        <h1 style=\"margin: 0; color: #495057;\">{{ $project->name }}</h1>
    </div>
    
    <div class=\"content\">
        <h2 style=\"color: #495057; margin-top: 0;\">Please confirm your subscription</h2>
        
        <p>Hello!</p>
        
        <p>Thank you for subscribing to our newsletter. To complete your subscription and start receiving updates from <strong>{{ $project->name }}</strong>, please click the confirmation button below:</p>
        
        <div style=\"text-align: center;\">
            <a href=\"{{ $confirmationUrl }}\" class=\"btn\">Confirm Subscription</a>
        </div>
        
        <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
        <p style=\"word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace;\">
            {{ $confirmationUrl }}
        </p>
        
        <p><strong>Important:</strong> This confirmation link will expire in {{ config('newsletter.confirmation_expiry_hours', 48) }} hours.</p>
        
        <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">
        
        <p style=\"font-size: 14px; color: #6c757d;\">
            If you didn't subscribe to this newsletter, you can safely ignore this email. Your email address will not be added to our mailing list.
        </p>
    </div>
    
    <div class=\"footer\">
        <p style=\"margin: 0;\">This email was sent by {{ config('app.name') }}</p>
        
        <div class=\"unsubscribe\">
            <p style=\"margin: 10px 0 0 0;\">
                Don't want to receive these emails? <a href=\"{{ $unsubscribeUrl }}\">Unsubscribe</a>
            </p>
        </div>
    </div>
</body>
</html>