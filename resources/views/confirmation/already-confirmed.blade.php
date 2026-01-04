<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Confirmed - {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Already Confirmed</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message }}
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Project:</strong> {{ $project->name }}
                </p>
                <p class="text-sm text-blue-800">
                    <strong>Status:</strong> Active Subscriber
                </p>
            </div>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500">
                    Your subscription is active and you'll continue to receive newsletters from this project.
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