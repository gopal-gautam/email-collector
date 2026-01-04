<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('JavaScript Snippet') }} - {{ $project->name }}
            </h2>
            <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Introduction -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">📝 Newsletter Subscription Snippet</h3>
                <p class="text-blue-800">
                    Copy and paste the JavaScript snippet below into your website to add a newsletter subscription form. 
                    The snippet will automatically create a subscription form and handle all the API communication.
                </p>
            </div>

            <!-- Configuration Options -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="container-id" class="block text-sm font-medium text-gray-700 mb-2">Container ID</label>
                            <input type="text" 
                                   id="container-id" 
                                   value="newsletter-signup" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSnippet()">
                            <p class="text-xs text-gray-500 mt-1">ID of the HTML element where the form will be rendered</p>
                        </div>
                        
                        <div>
                            <label for="button-text" class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                            <input type="text" 
                                   id="button-text" 
                                   value="Subscribe" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSnippet()">
                        </div>
                        
                        <div>
                            <label for="placeholder-text" class="block text-sm font-medium text-gray-700 mb-2">Placeholder Text</label>
                            <input type="text" 
                                   id="placeholder-text" 
                                   value="Enter your email address" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSnippet()">
                        </div>
                        
                        <div>
                            <label for="success-message" class="block text-sm font-medium text-gray-700 mb-2">Success Message</label>
                            <input type="text" 
                                   id="success-message" 
                                   value="Thank you for subscribing!" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSnippet()">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="custom-styling" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   onchange="updateSnippet()">
                            <label for="custom-styling" class="ml-2 block text-sm text-gray-900">
                                Include default styling (recommended for quick setup)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HTML Setup -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">1. HTML Setup</h3>
                    <p class="text-gray-700 mb-4">Add this HTML element to your page where you want the newsletter form to appear:</p>
                    
                    <div class="relative">
                        <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code id="html-code">&lt;div id="newsletter-signup"&gt;&lt;/div&gt;</code></pre>
                        <button onclick="copyToClipboard('html-code')" 
                                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- JavaScript Snippet -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">2. JavaScript Snippet</h3>
                    <p class="text-gray-700 mb-4">Add this script before the closing &lt;/body&gt; tag of your website:</p>
                    
                    <div class="relative">
                        <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto max-h-96" id="js-snippet"><code id="js-code">{{ $snippet }}</code></pre>
                        <button onclick="copyToClipboard('js-code')" 
                                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hosted Script Option -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">3. Alternative: Hosted Script</h3>
                    <p class="text-gray-700 mb-4">
                        For easier updates and maintenance, you can use our hosted script instead. 
                        This method automatically updates when we improve the script:
                    </p>
                    
                    <div class="relative mb-4">
                        <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code id="hosted-html">&lt;div id="newsletter-signup" 
     data-project-id="{{ $project->public_id }}"
     data-api-key="{{ $project->api_key }}"
     data-button-text="Subscribe"
     data-placeholder="Enter your email address"
     data-success-message="Thank you for subscribing!"&gt;
&lt;/div&gt;

&lt;script src="{{ url('/embed/newsletter.js') }}"&gt;&lt;/script&gt;</code></pre>
                        <button onclick="copyToClipboard('hosted-html')" 
                                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded">
                            Copy
                        </button>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">⚠️ Security Note</h4>
                        <p class="text-yellow-700 text-sm">
                            The hosted script includes your API key in the HTML. Make sure your CORS origins are properly configured 
                            to prevent unauthorized domains from using your API key. For maximum security, use the self-hosted snippet above.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Testing and Verification -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">4. Testing and Verification</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                    <span class="text-sm font-medium text-blue-600">1</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-semibold text-gray-900">Add your domain to allowed origins</h4>
                                <p class="text-sm text-gray-600">
                                    Make sure your website's domain is listed in the project's allowed origins to avoid CORS errors.
                                    <a href="{{ route('projects.edit', $project) }}" class="text-blue-600 hover:text-blue-800">Edit origins here</a>.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                    <span class="text-sm font-medium text-blue-600">2</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-semibold text-gray-900">Test the subscription form</h4>
                                <p class="text-sm text-gray-600">
                                    After implementing the snippet, test it with a real email address and verify the subscription 
                                    appears in your project dashboard.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                    <span class="text-sm font-medium text-blue-600">3</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-semibold text-gray-900">Monitor API requests</h4>
                                <p class="text-sm text-gray-600">
                                    Check the <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">project dashboard</a> 
                                    to monitor API requests and subscription activity.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent || element.innerText;
            
            navigator.clipboard.writeText(text).then(function() {
                // Visual feedback
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('bg-green-500', 'hover:bg-green-600');
                button.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-500', 'hover:bg-green-600');
                    button.classList.add('bg-blue-500', 'hover:bg-blue-600');
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy to clipboard');
            });
        }

        function updateSnippet() {
            const containerId = document.getElementById('container-id').value;
            const buttonText = document.getElementById('button-text').value;
            const placeholderText = document.getElementById('placeholder-text').value;
            const successMessage = document.getElementById('success-message').value;
            const includeStyles = document.getElementById('custom-styling').checked;
            
            // Update HTML code
            document.getElementById('html-code').textContent = `<div id="${containerId}"></div>`;
            
            // Generate updated JavaScript snippet
            const snippet = generateSnippet(containerId, buttonText, placeholderText, successMessage, includeStyles);
            document.getElementById('js-code').textContent = snippet;
            
            // Update hosted script HTML
            const hostedHtml = `<div id="${containerId}" 
     data-project-id="{{ $project->public_id }}"
     data-api-key="{{ $project->api_key }}"
     data-button-text="${buttonText}"
     data-placeholder="${placeholderText}"
     data-success-message="${successMessage}">
</div>

<script src="{{ url('/embed/newsletter.js') }}"></script>`;
            document.getElementById('hosted-html').textContent = hostedHtml;
        }

        function generateSnippet(containerId, buttonText, placeholderText, successMessage, includeStyles) {
            return `<script>
(function() {
    const container = document.getElementById('${containerId}');
    if (!container) {
        console.error('Newsletter container not found');
        return;
    }
    
    // Configuration
    const config = {
        projectId: '{{ $project->public_id }}',
        apiKey: '{{ $project->api_key }}',
        apiUrl: '{{ url('/api/v1/subscriptions') }}',
        buttonText: '${buttonText}',
        placeholder: '${placeholderText}',
        successMessage: '${successMessage}'
    };
    
    // Create form HTML
    const formHtml = \`
        <form id="newsletter-form" style="${includeStyles ? 'margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;' : ''}">
            <div style="${includeStyles ? 'display: flex; gap: 10px; align-items: center;' : ''}">
                <input type="email" 
                       id="newsletter-email" 
                       placeholder="\${config.placeholder}" 
                       required
                       style="${includeStyles ? 'flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;' : ''}" />
                <button type="submit" 
                        id="newsletter-submit"
                        style="${includeStyles ? 'padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;' : ''}">\${config.buttonText}</button>
            </div>
            <div id="newsletter-message" style="${includeStyles ? 'margin-top: 10px; font-size: 14px;' : ''}"></div>
        </form>
    \`;
    
    container.innerHTML = formHtml;
    
    // Handle form submission
    document.getElementById('newsletter-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('newsletter-email').value;
        const submitBtn = document.getElementById('newsletter-submit');
        const messageDiv = document.getElementById('newsletter-message');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Subscribing...';
        messageDiv.textContent = '';
        
        try {
            const response = await fetch(config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Project-ID': config.projectId,
                    'X-Api-Key': config.apiKey
                },
                body: JSON.stringify({ email: email })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                messageDiv.style.color = 'green';
                messageDiv.textContent = config.successMessage;
                document.getElementById('newsletter-email').value = '';
            } else {
                messageDiv.style.color = 'red';
                messageDiv.textContent = data.message || 'Subscription failed. Please try again.';
            }
        } catch (error) {
            messageDiv.style.color = 'red';
            messageDiv.textContent = 'Network error. Please try again.';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = config.buttonText;
        }
    });
})();
</script>`;
        }
    </script>
</x-app-layout>