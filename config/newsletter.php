<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Newsletter Collector Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Newsletter Collector application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Confirmation Email Settings
    |--------------------------------------------------------------------------
    |
    | Settings for email confirmation links and expiry.
    |
    */
    'confirmation_expiry_hours' => 48,

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options.
    |
    */
    'security' => [
        'max_projects_per_user' => 10,
        'api_key_length' => 64,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for API endpoints.
    |
    */
    'rate_limits' => [
        'subscriptions' => 30, // per minute
        'unsubscribe' => 10,   // per minute
        'general' => 60,       // per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Email-related configuration options.
    |
    */
    'email' => [
        'default_from_name' => env('NEWSLETTER_FROM_NAME', config('app.name')),
        'default_from_email' => env('NEWSLETTER_FROM_EMAIL', 'hello@example.com'),
        'templates' => [
            'confirmation' => 'emails.confirmation',
            'welcome' => 'emails.welcome',
            'admin_notification' => 'emails.admin-notification',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | Email validation and filtering options.
    |
    */
    'validation' => [
        'block_disposable_emails' => true,
        'block_role_emails' => true, // admin@, support@, etc.
        'custom_blocked_domains' => [
            // Add custom domains to block
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Settings for analytics and reporting.
    |
    */
    'analytics' => [
        'retention_days' => 365, // How long to keep analytics data
        'track_ip_addresses' => true,
        'track_user_agents' => true,
        'mask_ip_addresses' => true, // For privacy
    ],
];