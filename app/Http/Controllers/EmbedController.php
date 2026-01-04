<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class EmbedController extends Controller
{
    /**
     * Serve the newsletter JavaScript snippet
     */
    public function script(Request $request): Response
    {
        $cacheKey = 'newsletter_js_v2_' . config('app.env');
        $cacheDuration = config('newsletter.snippet.cache_duration', 3600);
        
        // Get or generate the JavaScript content - disable cache in development
        if (config('app.env') === 'production') {
            $jsContent = Cache::remember($cacheKey, $cacheDuration, function () {
                return $this->generateNewsletterScript();
            });
        } else {
            // Always fresh in development
            $jsContent = $this->generateNewsletterScript();
        }
        
        // Set appropriate headers
        $headers = [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => config('app.env') === 'production' 
                ? 'public, max-age=3600, immutable' 
                : 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Content-Type',
        ];
        
        if (config('app.env') === 'production') {
            $headers['ETag'] = '"' . md5($jsContent) . '"';
        }
        
        return response($jsContent, 200, $headers);
    }
    
    /**
     * Generate the newsletter JavaScript snippet
     */
    private function generateNewsletterScript(): string
    {
        $appUrl = config('app.url');
        $minify = config('newsletter.snippet.minify', false);
        $includeErrorReporting = config('newsletter.snippet.include_error_reporting', false);
        
        $js = $this->getJavaScriptTemplate($appUrl, $includeErrorReporting);
        
        if ($minify) {
            $js = $this->minifyJavaScript($js);
        }
        
        return $js;
    }
    
    /**
     * Get the JavaScript template
     */
    private function getJavaScriptTemplate(string $appUrl, bool $includeErrorReporting): string
    {
        $errorReporting = $includeErrorReporting ? $this->getErrorReportingCode() : '';
        
        return <<<JS
/**
 * Newsletter Collector JavaScript SDK - Data Attribute Version
 * Automatically handles newsletter subscriptions with data attributes
 * 
 * Usage Option 1 - Auto-initialization with container data attributes:
 * <div id="newsletter-signup" 
 *      data-project-id="YOUR_PROJECT_ID" 
 *      data-api-key="YOUR_API_KEY"
 *      data-button-text="Subscribe"
 *      data-placeholder="Enter your email">
 * </div>
 * <script src="{$appUrl}/embed/newsletter.js"></script>
 * 
 * Usage Option 2 - Form-based approach:
 * <form data-newsletter-form data-project-id="..." data-api-key="...">
 *   <input type="email" name="email" required>
 *   <button type="submit">Subscribe</button>
 *   <div data-newsletter-message></div>
 * </form>
 */
(function() {
    'use strict';
    
    // Default configuration
    const DEFAULT_CONFIG = {
        buttonText: 'Subscribe',
        placeholder: 'Enter your email address',
        successMessage: 'Thank you for subscribing!',
        errorMessage: 'Something went wrong. Please try again.',
        loadingMessage: 'Subscribing...',
        endpoint: '{$appUrl}/api/v1/subscriptions'
    };
    
    // CSS styles for the newsletter form
    const DEFAULT_STYLES = `
        .newsletter-form {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 400px;
            margin: 0 auto;
        }
        .newsletter-input-group {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }
        .newsletter-email-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s ease;
            outline: none;
        }
        .newsletter-email-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .newsletter-email-input.error {
            border-color: #ef4444;
        }
        .newsletter-submit-button {
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }
        .newsletter-submit-button:hover:not(:disabled) {
            background: #2563eb;
        }
        .newsletter-submit-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .newsletter-message {
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            margin-top: 12px;
            display: none;
        }
        .newsletter-message.newsletter-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            display: block;
        }
        .newsletter-message.newsletter-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            display: block;
        }
        .newsletter-message.newsletter-loading {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
            display: block;
        }
        @media (max-width: 480px) {
            .newsletter-input-group {
                flex-direction: column;
            }
            .newsletter-submit-button {
                width: 100%;
            }
        }
    `;
    
    // Inject styles
    function injectStyles() {
        if (document.getElementById('newsletter-collector-styles')) return;
        
        const styleSheet = document.createElement('style');
        styleSheet.id = 'newsletter-collector-styles';
        styleSheet.textContent = DEFAULT_STYLES;
        document.head.appendChild(styleSheet);
    }
    
    // Newsletter form creator
    function createNewsletterForm(container, config) {
        if (!container) {
            console.error('Newsletter container not found');
            return null;
        }
        
        // Validate that the container is a proper element (not script, style, etc.)
        if (container.tagName === 'SCRIPT' || container.tagName === 'STYLE' || container.tagName === 'META') {
            console.error('Newsletter container cannot be a script, style, or meta element:', container.tagName);
            return null;
        }
        
        console.log('Newsletter Collector: Creating form in element:', {
            tagName: container.tagName,
            id: container.id,
            className: container.className
        });
        
        try {
            // Create form HTML with proper template substitution
            const formHtml = '<form class="newsletter-form" data-newsletter-form>' +
                '<div class="newsletter-input-group">' +
                '<input type="email" name="email" class="newsletter-email-input" ' +
                'placeholder="' + config.placeholder + '" required autocomplete="email" />' +
                '<button type="submit" class="newsletter-submit-button">' +
                config.buttonText + '</button>' +
                '</div>' +
                '<div class="newsletter-message" data-newsletter-message></div>' +
                '</form>';
            
            container.innerHTML = formHtml;
            console.log('Newsletter Collector: HTML inserted successfully');
            
            // Set up the form
            const form = container.querySelector('[data-newsletter-form]');
            if (form) {
                setupForm(form, config);
                console.log('Newsletter form created successfully');
            } else {
                console.error('Failed to create newsletter form - form element not found after creation');
                console.error('Container innerHTML after insertion:', container.innerHTML);
            }
            
            return form;
        } catch (error) {
            console.error('Error creating newsletter form:', error);
            console.error('Container element:', container);
            return null;
        }
    }
    
    // Newsletter form handler
    function setupForm(form, config) {
        if (!form) {
            console.error('Newsletter form not found');
            return;
        }
        
        const messageEl = form.querySelector('[data-newsletter-message]') || 
                         document.querySelector('[data-newsletter-message]');
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            handleSubmit(form, messageEl, config);
        });
    }
    
    // Handle form submission
    function handleSubmit(form, messageEl, config) {
        const formData = new FormData(form);
        const email = formData.get('email');
        
        if (!email || !isValidEmail(email)) {
            showMessage(messageEl, 'Please enter a valid email address.', 'error');
            return;
        }
        
        // Show loading state
        showMessage(messageEl, config.loadingMessage, 'loading');
        setFormState(form, true);
        
        // Prepare request data
        const requestData = {
            email: email,
            source_url: window.location.href,
            referrer: document.referrer,
            meta: {
                form_type: 'embed_script',
                user_agent: navigator.userAgent,
                timestamp: new Date().toISOString()
            }
        };
        
        // Make the API request
        fetch(config.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Project-ID': config.projectId,
                'X-Api-Key': config.apiKey,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json().then(data => ({ response, data })))
        .then(({ response, data }) => {
            setFormState(form, false);
            
            if (response.ok && data.success) {
                const message = data.data?.requires_confirmation 
                    ? 'Please check your email to confirm your subscription.'
                    : config.successMessage;
                
                showMessage(messageEl, message, 'success');
                form.reset();
                
                // Trigger custom event
                triggerEvent(form, 'newsletter:success', { data });
            } else {
                throw new Error(data.message || 'Subscription failed');
            }
        })
        .catch(error => {
            setFormState(form, false);
            showMessage(messageEl, config.errorMessage, 'error');
            
            // Trigger custom event
            triggerEvent(form, 'newsletter:error', { error });
            
            {$errorReporting}
        });
    }
    
    // Utility functions
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function showMessage(element, message, type) {
        if (!element) return;
        element.textContent = message;
        element.className = 'newsletter-message newsletter-' + type;
    }
    
    function setFormState(form, loading) {
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitButton) {
            submitButton.disabled = loading;
            submitButton.textContent = loading ? 'Subscribing...' : 
                (submitButton.dataset.originalText || submitButton.textContent);
            if (!loading && !submitButton.dataset.originalText) {
                submitButton.dataset.originalText = submitButton.textContent;
            }
        }
    }
    
    function triggerEvent(element, eventName, detail) {
        const event = new CustomEvent(eventName, {
            detail: detail,
            bubbles: true,
            cancelable: true
        });
        element.dispatchEvent(event);
    }
    
    // Auto-initialization
    function autoInit() {
        try {
            console.log('Newsletter Collector: Starting auto-initialization');
            console.log('Newsletter Collector: Document ready state:', document.readyState);
            injectStyles();
            
            // Method 1: Auto-create forms in containers with data attributes
            // Exclude script, style, meta, link, and other non-container elements
            const containers = document.querySelectorAll('div[data-project-id][data-api-key]:not([data-newsletter-form]), section[data-project-id][data-api-key]:not([data-newsletter-form]), aside[data-project-id][data-api-key]:not([data-newsletter-form])');
            console.log('Newsletter Collector: Found ' + containers.length + ' containers for auto-creation');
            console.log('Newsletter Collector: Containers:', containers);
            
            containers.forEach((container, index) => {
                console.log('Newsletter Collector: Processing container ' + (index + 1) + ':', {
                    id: container.id,
                    projectId: container.dataset.projectId,
                    apiKey: container.dataset.apiKey ? 'present' : 'missing',
                    element: container
                });
                
                const config = {
                    ...DEFAULT_CONFIG,
                    projectId: container.dataset.projectId,
                    apiKey: container.dataset.apiKey,
                    buttonText: container.dataset.buttonText || DEFAULT_CONFIG.buttonText,
                    placeholder: container.dataset.placeholder || DEFAULT_CONFIG.placeholder,
                    successMessage: container.dataset.successMessage || DEFAULT_CONFIG.successMessage,
                    errorMessage: container.dataset.errorMessage || DEFAULT_CONFIG.errorMessage,
                    loadingMessage: container.dataset.loadingMessage || DEFAULT_CONFIG.loadingMessage
                };
                
                console.log('Newsletter Collector: Config for container ' + (index + 1) + ':', config);
                
                if (config.projectId && config.apiKey) {
                    console.log('Newsletter Collector: Creating form with config:', config);
                    const result = createNewsletterForm(container, config);
                    console.log('Newsletter Collector: Form creation result:', result ? 'success' : 'failed');
                    if (result) {
                        console.log('Newsletter Collector: Form HTML:', container.innerHTML.substring(0, 200) + '...');
                    }
                } else {
                    console.warn('Newsletter Collector: Skipping container due to missing projectId or apiKey');
                }
            });
            
            // Method 2: Setup existing forms with data attributes
            const forms = document.querySelectorAll('[data-newsletter-form][data-project-id][data-api-key]');
            console.log('Newsletter Collector: Found ' + forms.length + ' existing forms to setup');
            
            forms.forEach((form, index) => {
                console.log('Newsletter Collector: Processing existing form ' + (index + 1));
                
                const config = {
                    ...DEFAULT_CONFIG,
                    projectId: form.dataset.projectId,
                    apiKey: form.dataset.apiKey,
                    buttonText: form.dataset.buttonText || DEFAULT_CONFIG.buttonText,
                    placeholder: form.dataset.placeholder || DEFAULT_CONFIG.placeholder,
                    successMessage: form.dataset.successMessage || DEFAULT_CONFIG.successMessage,
                    errorMessage: form.dataset.errorMessage || DEFAULT_CONFIG.errorMessage,
                    loadingMessage: form.dataset.loadingMessage || DEFAULT_CONFIG.loadingMessage
                };
                
                if (config.projectId && config.apiKey) {
                    setupForm(form, config);
                } else {
                    console.warn('Newsletter Collector: Skipping form due to missing projectId or apiKey');
                }
            });
            
            console.log('Newsletter Collector: Auto-initialization completed');
        } catch (error) {
            console.error('Newsletter Collector initialization error:', error);
            console.error('Newsletter Collector initialization error stack:', error.stack);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }
    
    // Expose for manual use
    window.NewsletterCollector = {
        init: autoInit,
        createForm: createNewsletterForm,
        setupForm: setupForm
    };
    
})();
JS;
    }
    
    /**
     * Get error reporting code
     */
    private function getErrorReportingCode(): string
    {
        return "console.error('Newsletter subscription error:', error);";
    }
    
    /**
     * Basic JavaScript minification
     */
    private function minifyJavaScript(string $js): string
    {
        // Remove comments
        $js = preg_replace('/\/\*[^*]*\*+(?:[^*\/][^*]*\*+)*\//', '', $js);
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove extra whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/;\s*}/', '}', $js);
        
        return trim($js);
    }
}
