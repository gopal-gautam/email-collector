/**
 * Newsletter Collector JavaScript SDK
 * A lightweight, dependency-free script for collecting newsletter subscriptions
 * 
 * Usage:
 * 1. Include this script in your website
 * 2. Configure the settings object below
 * 3. Call NewsletterCollector.init() when DOM is ready
 */

(function(window, document) {
    'use strict';

    // Configuration - Update these values for your project
    const CONFIG = {
        projectId: '{{PROJECT_ID}}',
        apiKey: '{{API_KEY}}',
        apiUrl: '{{API_URL}}',
        containerId: '{{CONTAINER_ID}}',
        
        // UI Configuration
        buttonText: '{{BUTTON_TEXT}}',
        placeholder: '{{PLACEHOLDER}}',
        successMessage: '{{SUCCESS_MESSAGE}}',
        errorMessage: 'Something went wrong. Please try again.',
        
        // Email validation
        validateEmail: true,
        
        // Styling
        includeDefaultStyles: {{INCLUDE_STYLES}},
        
        // Callbacks
        onSuccess: null,
        onError: null,
        
        // Debug mode
        debug: false
    };

    // Newsletter Collector Class
    class NewsletterCollector {
        constructor(config) {
            this.config = { ...CONFIG, ...config };
            this.container = null;
            this.form = null;
            this.emailInput = null;
            this.submitButton = null;
            this.messageDiv = null;
            this.isSubmitting = false;
        }

        // Initialize the newsletter collector
        init() {
            this.log('Initializing Newsletter Collector...');
            
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        // Setup the newsletter form
        setup() {
            this.container = document.getElementById(this.config.containerId);
            
            if (!this.container) {
                this.error(`Container with ID '${this.config.containerId}' not found`);
                return;
            }

            this.createForm();
            this.attachEvents();
            this.log('Newsletter Collector initialized successfully');
        }

        // Create the subscription form HTML
        createForm() {
            const styles = this.config.includeDefaultStyles ? this.getDefaultStyles() : '';
            
            try {
                this.container.innerHTML = `
                    ${styles}
                    <form class="newsletter-form" id="newsletter-form-${this.config.containerId}">
                        <div class="newsletter-input-group">
                            <input 
                                type="email" 
                                class="newsletter-email-input" 
                                id="newsletter-email-${this.config.containerId}"
                                placeholder="${this.config.placeholder}" 
                                required
                                autocomplete="email"
                            />
                            <button 
                                type="submit" 
                                class="newsletter-submit-button"
                                id="newsletter-submit-${this.config.containerId}"
                            >
                                ${this.config.buttonText}
                            </button>
                        </div>
                        <div class="newsletter-message" id="newsletter-message-${this.config.containerId}"></div>
                    </form>
                `;

                // Cache form elements
                this.form = document.getElementById(`newsletter-form-${this.config.containerId}`);
                this.emailInput = document.getElementById(`newsletter-email-${this.config.containerId}`);
                this.submitButton = document.getElementById(`newsletter-submit-${this.config.containerId}`);
                this.messageDiv = document.getElementById(`newsletter-message-${this.config.containerId}`);
                
                if (!this.form || !this.emailInput || !this.submitButton || !this.messageDiv) {
                    throw new Error('Failed to create form elements');
                }
            } catch (error) {
                this.error('Error creating form:', error);
            }
        }

        // Attach event listeners
        attachEvents() {
            if (!this.form) {
                this.error('Form element not found, cannot attach events');
                return;
            }
            
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            
            // Clear messages when user starts typing
            if (this.emailInput) {
                this.emailInput.addEventListener('input', () => {
                    this.clearMessage();
                    this.emailInput.classList.remove('newsletter-error');
                });
            }
        }

        // Handle form submission
        async handleSubmit(event) {
            event.preventDefault();
            
            if (this.isSubmitting) return;
            
            const email = this.emailInput.value.trim();
            
            // Validate email
            if (!email) {
                this.showError('Please enter your email address.');
                return;
            }
            
            if (this.config.validateEmail && !this.isValidEmail(email)) {
                this.showError('Please enter a valid email address.');
                this.emailInput.classList.add('newsletter-error');
                return;
            }

            await this.submitSubscription(email);
        }

        // Submit subscription to API
        async submitSubscription(email) {
            this.isSubmitting = true;
            this.setSubmitState(true);
            this.clearMessage();

            try {
                const response = await fetch(this.config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Project-ID': this.config.projectId,
                        'X-Api-Key': this.config.apiKey,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        email: email,
                        source_url: window.location.href,
                        referrer: document.referrer
                    })
                });

                const data = await response.json();
                
                if (response.ok) {
                    this.handleSuccess(data);
                } else {
                    this.handleError(data);
                }
                
            } catch (error) {
                this.log('Network error:', error);
                this.showError('Network error. Please check your connection and try again.');
                
                if (this.config.onError) {
                    this.config.onError(error);
                }
            } finally {
                this.isSubmitting = false;
                this.setSubmitState(false);
            }
        }

        // Handle successful subscription
        handleSuccess(data) {
            this.showSuccess(this.config.successMessage);
            this.emailInput.value = '';
            
            this.log('Subscription successful:', data);
            
            if (this.config.onSuccess) {
                this.config.onSuccess(data);
            }
        }

        // Handle subscription error
        handleError(data) {
            const message = data.message || this.config.errorMessage;
            this.showError(message);
            
            if (data.errors && data.errors.email) {
                this.emailInput.classList.add('newsletter-error');
            }
            
            this.log('Subscription error:', data);
            
            if (this.config.onError) {
                this.config.onError(data);
            }
        }

        // Set submit button state
        setSubmitState(isSubmitting) {
            this.submitButton.disabled = isSubmitting;
            this.submitButton.textContent = isSubmitting ? 'Subscribing...' : this.config.buttonText;
            
            if (isSubmitting) {
                this.submitButton.classList.add('newsletter-loading');
            } else {
                this.submitButton.classList.remove('newsletter-loading');
            }
        }

        // Show success message
        showSuccess(message) {
            this.messageDiv.className = 'newsletter-message newsletter-success';
            this.messageDiv.textContent = message;
            this.messageDiv.style.display = 'block';
        }

        // Show error message
        showError(message) {
            this.messageDiv.className = 'newsletter-message newsletter-error';
            this.messageDiv.textContent = message;
            this.messageDiv.style.display = 'block';
        }

        // Clear message
        clearMessage() {
            this.messageDiv.textContent = '';
            this.messageDiv.style.display = 'none';
            this.messageDiv.className = 'newsletter-message';
        }

        // Email validation
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Logging
        log(...args) {
            if (this.config.debug) {
                console.log('[Newsletter Collector]', ...args);
            }
        }

        // Error logging
        error(...args) {
            console.error('[Newsletter Collector]', ...args);
        }

        // Get default CSS styles
        getDefaultStyles() {
            return `
                <style>
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
                
                .newsletter-email-input.newsletter-error {
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
                
                .newsletter-submit-button.newsletter-loading {
                    background: #6b7280;
                }
                
                .newsletter-message {
                    padding: 12px;
                    border-radius: 6px;
                    font-size: 14px;
                    text-align: center;
                    display: none;
                }
                
                .newsletter-message.newsletter-success {
                    background: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                }
                
                .newsletter-message.newsletter-error {
                    background: #fee2e2;
                    color: #dc2626;
                    border: 1px solid #fca5a5;
                }
                
                @media (max-width: 480px) {
                    .newsletter-input-group {
                        flex-direction: column;
                    }
                    
                    .newsletter-submit-button {
                        width: 100%;
                    }
                }
                </style>
            `;
        }
    }

    // Auto-initialize if container exists
    function autoInit() {
        try {
            // Check for data attributes on existing containers
            const containers = document.querySelectorAll('[data-newsletter-collector]');
            
            containers.forEach(container => {
                try {
                    const config = {
                        containerId: container.id,
                        projectId: container.dataset.projectId || CONFIG.projectId,
                        apiKey: container.dataset.apiKey || CONFIG.apiKey,
                        apiUrl: container.dataset.apiUrl || CONFIG.apiUrl,
                        buttonText: container.dataset.buttonText || CONFIG.buttonText,
                        placeholder: container.dataset.placeholder || CONFIG.placeholder,
                        successMessage: container.dataset.successMessage || CONFIG.successMessage,
                        includeDefaultStyles: container.dataset.includeStyles !== 'false'
                    };
                    
                    if (!container.id) {
                        console.error('Newsletter container missing required id attribute');
                        return;
                    }
                    
                    const collector = new NewsletterCollector(config);
                    collector.init();
                } catch (error) {
                    console.error('Error initializing newsletter collector for container:', container, error);
                }
            });
        } catch (error) {
            console.error('Error during newsletter collector auto-initialization:', error);
        }
    }

    // Expose to global scope
    window.NewsletterCollector = NewsletterCollector;
    
    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

    // Manual initialization function
    window.initNewsletterCollector = function(config) {
        const collector = new NewsletterCollector(config);
        collector.init();
        return collector;
    };

})(window, document);