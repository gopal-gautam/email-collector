<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resubscribe - {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Resubscribe</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message }}
            </p>
            
            <div class="bg-orange-50 border border-orange-200 rounded-md p-4 mb-6">
                <p class="text-sm text-orange-800">
                    <strong>Project:</strong> {{ $project->name }}
                </p>
                <p class="text-sm text-orange-800">
                    <strong>Current Status:</strong> Unsubscribed
                </p>
            </div>
            
            <form method="POST" action="{{ route('confirm-subscription', ['subscription' => $subscription->id]) }}">
                @csrf
                <input type="hidden" name="action" value="resubscribe">
                
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200">
                    Yes, Resubscribe Me
                </button>
            </form>
            
            <div class="mt-6 space-y-4">
                <p class="text-sm text-gray-500">
                    Click the button above to reactivate your subscription and start receiving newsletters again.
                </p>
                
                @if($project->user)
                <p class="text-xs text-gray-400">
                    This subscription is managed by {{ $project->user->name }}
                </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>