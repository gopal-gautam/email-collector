<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Confirmed - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #52c234 0%, #061700 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .success-icon {
            font-size: 64px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .project-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Subscription Confirmed!</h1>
        <p>{{ $message ?? 'Your subscription has been confirmed successfully!' }}</p>
        <p>You are now subscribed to <span class="project-name">{{ $project->name ?? 'our newsletter' }}</span> and will receive updates directly to your inbox.</p>
        <p>Thank you for subscribing!</p>
        <a href="{{ url('/') }}" class="btn">Continue</a>
    </div>
</body>
</html>