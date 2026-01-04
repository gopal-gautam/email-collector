<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Undeliverable - {{ $project->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Email Undeliverable</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message }}
            </p>
            
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <p class="text-sm text-red-800">
                    <strong>Project:</strong> {{ $project->name }}
                </p>
                <p class="text-sm text-red-800">
                    <strong>Status:</strong> Bounced
                </p>
            </div>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500">
                    This email address has been marked as undeliverable because previous emails sent to it have bounced. 
                    This could be due to:
                </p>
                
                <ul class="text-sm text-gray-500 text-left list-disc list-inside space-y-1">
                    <li>The email address no longer exists</li>
                    <li>The mailbox is full</li>
                    <li>The email server is blocking our messages</li>
                    <li>The email address was mistyped during signup</li>
                </ul>
                
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <p class="text-sm text-gray-700">
                        <strong>Need help?</strong> If you believe this is an error, please contact the project owner.
                    </p>
                    @if($project->user)
                    <p class="text-xs text-gray-500 mt-2">
                        This subscription is managed by {{ $project->user->name }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>