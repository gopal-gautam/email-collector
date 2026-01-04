<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Newsletter Test Page</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Newsletter Test</h1>
            
            <!-- Test Container for Newsletter Form -->
            <div id="newsletter-test" 
                 data-project-id="test-project" 
                 data-api-key="test-key"
                 data-button-text="Subscribe"
                 data-placeholder="Enter your email"
                 data-success-message="Thank you for subscribing!">
                <!-- Newsletter form will be inserted here by the script -->
            </div>
            
            <div class="mt-6 text-sm text-gray-600">
                <p><strong>Debug Info:</strong></p>
                <ul class="list-disc list-inside">
                    <li>Container ID: newsletter-test</li>
                    <li>Project ID: test-project</li>
                    <li>API Key: test-key</li>
                    <li>Cache Buster: {{ now()->timestamp }}</li>
                </ul>
                
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p class="text-yellow-800"><strong>Status:</strong></p>
                    <div id="status-container">
                        <div class="flex items-center mt-2">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></div>
                            <span>Initializing newsletter collector...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Collector Script with cache busting -->
    <script src="{{ url('/embed/newsletter.js') }}?v={{ now()->timestamp }}"></script>
    
    <!-- Debug Script -->
    <script>
        console.log('Test page loaded at:', new Date());
        console.log('Cache busting timestamp:', {{ now()->timestamp }});
        
        // Add comprehensive error event listener
        window.addEventListener('error', function(e) {
            console.error('=== JavaScript Error Detected ===');
            console.error('Message:', e.message);
            console.error('Filename:', e.filename);
            console.error('Line:', e.lineno);
            console.error('Column:', e.colno);
            console.error('Error object:', e.error);
            console.error('Stack:', e.error?.stack);
            
            // Show error on page
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = 'background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; padding: 12px; margin: 12px 0; border-radius: 6px;';
            errorDiv.innerHTML = `<strong>JavaScript Error:</strong> ${e.message} at line ${e.lineno}`;
            document.body.appendChild(errorDiv);
            
            // Update status
            updateStatus('error', `JavaScript Error: ${e.message}`);
        });
        
        function updateStatus(type, message) {
            const statusContainer = document.getElementById('status-container');
            if (!statusContainer) return;
            
            const colors = {
                success: { bg: 'bg-green-400', text: 'text-green-800', border: 'border-green-200' },
                error: { bg: 'bg-red-400', text: 'text-red-800', border: 'border-red-200' },
                warning: { bg: 'bg-yellow-400', text: 'text-yellow-800', border: 'border-yellow-200' },
                info: { bg: 'bg-blue-400', text: 'text-blue-800', border: 'border-blue-200' }
            };
            
            const color = colors[type] || colors.info;
            
            statusContainer.innerHTML = `
                <div class="flex items-center mt-2">
                    <div class="w-3 h-3 ${color.bg} rounded-full mr-2"></div>
                    <span class="${color.text}">${message}</span>
                </div>
            `;
        }
        
        // Check if newsletter collector initialized
        setTimeout(() => {
            console.log('=== Newsletter Collector Status Check ===');
            
            const container = document.getElementById('newsletter-test');
            console.log('Container found:', !!container);
            console.log('Container content length:', container?.innerHTML?.length || 0);
            
            if (container && container.innerHTML.trim()) {
                console.log('✅ Newsletter form created successfully');
                const form = container.querySelector('form');
                const input = container.querySelector('input[type="email"]');
                const button = container.querySelector('button');
                console.log('Form elements found:', { form: !!form, input: !!input, button: !!button });
                
                updateStatus('success', 'Newsletter form initialized successfully!');
            } else {
                console.error('❌ Newsletter form not created');
                updateStatus('error', 'Newsletter form failed to initialize');
            }
            
            // Check for global objects
            console.log('Window.NewsletterCollector exists:', !!window.NewsletterCollector);
            
        }, 2000);
        
        // Test manual initialization
        setTimeout(() => {
            console.log('=== Testing Manual Initialization ===');
            try {
                if (window.NewsletterCollector) {
                    window.NewsletterCollector.init();
                    console.log('✅ Manual initialization successful');
                } else {
                    console.error('❌ NewsletterCollector not available');
                }
            } catch (error) {
                console.error('❌ Manual initialization failed:', error);
            }
        }, 3000);
    </script>
</body>
</html>