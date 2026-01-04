<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Confirmation Link</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Invalid Confirmation</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message }}
            </p>
            
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <p class="text-sm text-red-800">
                    This confirmation link is not valid. This could be because:
                </p>
                <ul class="text-sm text-red-800 mt-2 list-disc list-inside text-left">
                    <li>The link has expired</li>
                    <li>The link was already used</li>
                    <li>The link was corrupted or incomplete</li>
                    <li>The subscription no longer exists</li>
                </ul>
            </div>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500">
                    If you're trying to confirm a newsletter subscription, you may need to sign up again 
                    or request a new confirmation email.
                </p>
                
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <p class="text-sm text-gray-700">
                        <strong>Need help?</strong> Contact the website owner if you continue to have problems.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>