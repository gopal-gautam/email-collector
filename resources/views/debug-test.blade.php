<!DOCTYPE html>
<html>
<head>
    <title>Debug Newsletter Test</title>
    <style>
        .debug-box { background: #f3f4f6; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .error { background: #fee2e2; color: #dc2626; }
        .success { background: #d1fae5; color: #065f46; }
        .container-box { border: 2px dashed #6b7280; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Debug Newsletter Test</h1>
    
    <div class="debug-box">
        <h3>Before Script Load</h3>
        <p>Looking for containers with selector: <code>div[data-project-id][data-api-key]:not([data-newsletter-form])</code></p>
    </div>
    
    <div class="container-box">
        <h3>Newsletter Container</h3>
        <div id="newsletter-signup" 
             data-project-id="test-project-123" 
             data-api-key="test-key-456"
             data-button-text="Join Newsletter"
             data-placeholder="Your email address">
            <p>This container should be replaced with a newsletter form...</p>
        </div>
    </div>

    <div id="debug-output" class="debug-box">
        <h3>Debug Output:</h3>
        <div id="logs"></div>
    </div>

    <script>
        // Manual selector test before loading newsletter script
        const testContainers = document.querySelectorAll('div[data-project-id][data-api-key]:not([data-newsletter-form])');
        const allDataElements = document.querySelectorAll('[data-project-id][data-api-key]');
        
        console.log('=== MANUAL SELECTOR TEST ===');
        console.log('div[data-project-id][data-api-key]:not([data-newsletter-form]) found:', testContainers.length);
        console.log('Elements:', testContainers);
        console.log('[data-project-id][data-api-key] found:', allDataElements.length);
        console.log('All elements:', allDataElements);
        
        // Log element details
        allDataElements.forEach((el, index) => {
            console.log(`Element ${index + 1}:`, {
                tagName: el.tagName,
                id: el.id,
                className: el.className,
                projectId: el.dataset.projectId,
                apiKey: el.dataset.apiKey
            });
        });
        
        const logContainer = document.getElementById('logs');
        
        function addLog(message, type = 'debug') {
            const logDiv = document.createElement('div');
            logDiv.className = 'debug-box ' + type;
            logDiv.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            logContainer.appendChild(logDiv);
            console.log(message);
        }
        
        addLog(`Manual test found ${testContainers.length} div containers`);
        addLog(`Manual test found ${allDataElements.length} total data elements`);
        
        allDataElements.forEach((el, index) => {
            addLog(`Element ${index + 1}: ${el.tagName}#${el.id || 'no-id'}.${el.className || 'no-class'}`);
        });
    </script>

    <!-- Load newsletter script -->
    <script src="{{ url('/embed/newsletter.js') }}?v={{ now()->timestamp }}"></script>

    <script>
        // Check result after newsletter script loads
        setTimeout(() => {
            const container = document.getElementById('newsletter-signup');
            if (container && container.innerHTML.includes('newsletter-form')) {
                addLog('✅ Newsletter form created successfully!', 'success');
            } else {
                addLog('❌ Newsletter form creation failed', 'error');
                if (container) {
                    addLog(`Container content: ${container.innerHTML.substring(0, 100)}...`);
                }
            }
        }, 1000);
    </script>
</body>
</html>