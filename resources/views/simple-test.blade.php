<!DOCTYPE html>
<html>
<head>
    <title>Simple Newsletter Test</title>
    <style>
        .debug { font-family: monospace; background: #f3f4f6; padding: 10px; margin: 10px 0; }
        .error { background: #fee2e2; color: #dc2626; }
        .success { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <h1>Simple Newsletter Test</h1>
    
    <div id="newsletter-test" 
         data-project-id="test-project" 
         data-api-key="test-key"
         data-button-text="Subscribe"
         data-placeholder="Enter your email">
        <!-- Newsletter form will be inserted here -->
    </div>

    <div id="debug-output">
        <h3>Debug Output:</h3>
        <div id="logs"></div>
    </div>

    <!-- Test basic JavaScript first -->
    <script>
        console.log('=== BASIC TEST START ===');
        
        const logContainer = document.getElementById('logs');
        
        function addLog(message, type = 'debug') {
            const logDiv = document.createElement('div');
            logDiv.className = type;
            logDiv.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            if (logContainer) {
                logContainer.appendChild(logDiv);
            }
            console.log(message);
        }
        
        addLog('JavaScript is working');
        addLog('DOM loaded successfully');
        
        // Test if the container exists
        const container = document.getElementById('newsletter-test');
        if (container) {
            addLog('Container found: ' + container.id, 'success');
            addLog('Container data attributes: ' + JSON.stringify(container.dataset));
        } else {
            addLog('Container NOT found!', 'error');
        }
        
        // Track script loading
        let newsletterScriptLoaded = false;
        
        window.addEventListener('load', function() {
            addLog('Window load event fired');
            
            // Check script tag
            const scripts = document.querySelectorAll('script[src*="newsletter.js"]');
            addLog('Newsletter script tags found: ' + scripts.length);
            
            setTimeout(() => {
                addLog('=== 3 SECOND CHECK ===');
                addLog('NewsletterCollector exists: ' + !!window.NewsletterCollector);
                
                const container = document.getElementById('newsletter-test');
                if (container) {
                    addLog('Container content: "' + container.innerHTML + '"');
                    addLog('Container content length: ' + container.innerHTML.length);
                } else {
                    addLog('Container disappeared!', 'error');
                }
            }, 3000);
        });
        
        console.log('=== BASIC TEST END ===');
    </script>

    <!-- Load newsletter script -->
    <script>
        addLog('About to load newsletter script...');
    </script>
    <script src="{{ url('/embed/newsletter.js') }}?v={{ now()->timestamp }}" onload="addLog('Newsletter script loaded successfully', 'success');" onerror="addLog('Newsletter script failed to load!', 'error');"></script>
    <script>
        addLog('Newsletter script tag processed');
    </script>
</body>
</html>