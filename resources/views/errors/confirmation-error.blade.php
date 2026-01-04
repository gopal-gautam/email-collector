<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Error - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
        .error-icon {
            font-size: 64px;
            color: #e74c3c;
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
        .btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s;
            margin: 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-secondary {
            background: #95a5a6;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">❌</div>
        <h1>Confirmation Error</h1>
        <p>{{ $message ?? 'There was an error confirming your subscription.' }}</p>
        <p>This could happen if:</p>
        <ul style="text-align: left; color: #7f8c8d; margin: 20px 0;">
            <li>The confirmation link has expired</li>
            <li>The link has already been used</li>
            <li>The link is invalid or corrupted</li>
        </ul>
        <div>
            <a href="{{ url('/') }}" class="btn btn-secondary">Return to Homepage</a>
            @if(isset($project) && $project)
                <a href="{{ route('api.subscribe', ['project' => $project]) }}" class="btn">Try Subscribing Again</a>
            @endif
        </div>
    </div>
</body>
</html>