<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resubscribed Successfully - {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome Back!</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message }}
            </p>
            
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <p class="text-sm text-green-800">
                    <strong>Project:</strong> {{ $project->name }}
                </p>
                <p class="text-sm text-green-800">
                    <strong>Status:</strong> Active Subscriber
                </p>
            </div>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500">
                    You'll start receiving newsletters from this project again. 
                    @if($project->welcome_email)
                    You should receive a welcome email shortly.
                    @endif
                </p>
                
                @if($project->user)
                <p class="text-xs text-gray-400">
                    This subscription is managed by {{ $project->user->name }}
                </p>
                @endif
                
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-400">
                        If you ever want to unsubscribe again, look for the unsubscribe link in any newsletter email.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>