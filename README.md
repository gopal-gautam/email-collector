# Newsletter Collector Laravel

A comprehensive Laravel-based newsletter subscription system with API endpoints, dashboard management, and JavaScript integration for collecting newsletter subscriptions from any website.

## Features

- 🚀 **RESTful API** - Clean API endpoints for subscription management
- 🔐 **Project-based Authentication** - Secure API key system per project
- 📧 **Email System** - Double opt-in, welcome emails, and admin notifications
- 🎨 **Dashboard** - Modern web interface for project management
- 📊 **Analytics** - Track subscriptions, API requests, and growth metrics
- 🔒 **Security** - CORS protection, rate limiting, and request logging
- 🛡️ **Privacy-focused** - IP masking and secure unsubscribe handling
- 📱 **JavaScript SDK** - Easy integration with any website
- 📈 **Export** - CSV export functionality for subscribers
- ⚡ **Queue System** - Background email processing

## Requirements

- PHP 8.2 or higher
- Composer
- Laravel 11
- MySQL, PostgreSQL, SQLite, or SQL Server
- Mail server (SMTP/Mailgun/SES/etc.)

## Installation

### 1. Clone and Install Dependencies

```bash
# Clone the repository
git clone <repository-url> newsletter-collector
cd newsletter-collector

# Install PHP dependencies
composer install

# Install Node.js dependencies (for frontend assets)
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit `.env` file with your settings:

```env
# Application
APP_NAME="Newsletter Collector"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=newsletter_collector
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue (use 'database' for simple setup)
QUEUE_CONNECTION=database

# Newsletter Specific Settings
NEWSLETTER_DOUBLE_OPT_IN_DEFAULT=true
NEWSLETTER_CONFIRMATION_EXPIRY_HOURS=48
NEWSLETTER_ADMIN_EMAIL=admin@your-domain.com
NEWSLETTER_REPLY_TO_EMAIL=noreply@your-domain.com
NEWSLETTER_RATE_LIMIT_SUBSCRIPTIONS=30
NEWSLETTER_RATE_LIMIT_UNSUBSCRIBE=10
NEWSLETTER_MAX_PROJECTS_PER_USER=10
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed
```

### 5. Build Frontend Assets

```bash
# Build for production
npm run build

# Or for development
npm run dev
```

### 6. Configure Web Server

#### Apache (.htaccess)
The included `.htaccess` file should work out of the box.

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/newsletter-collector/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 7. Queue Worker (Production)

Set up a queue worker for email processing:

```bash
# Install supervisor (Ubuntu/Debian)
sudo apt install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/newsletter-collector.conf
```

Supervisor configuration:
```ini
[program:newsletter-collector]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/newsletter-collector/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/newsletter-collector/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start newsletter-collector:*
```

### 8. Task Scheduling (Production)

Add to your crontab:
```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /var/www/newsletter-collector && php artisan schedule:run >> /dev/null 2>&1
```

## Usage

### Dashboard Access

1. Register a new account at `/register`
2. Verify your email address
3. Create your first newsletter project
4. Configure CORS origins for your website domains
5. Copy the JavaScript snippet or use the API directly

### API Usage

#### Subscribe to Newsletter

```bash
curl -X POST https://your-domain.com/api/v1/subscriptions \
  -H "Content-Type: application/json" \
  -H "X-Project-ID: your-project-id" \
  -H "X-Api-Key: your-api-key" \
  -d '{"email": "user@example.com"}'
```

#### Unsubscribe

```bash
curl -X POST https://your-domain.com/api/v1/unsubscribe \
  -H "Content-Type: application/json" \
  -H "X-Project-ID: your-project-id" \
  -H "X-Api-Key: your-api-key" \
  -d '{"email": "user@example.com"}'
```

#### Health Check

```bash
curl https://your-domain.com/api/v1/health
```

### JavaScript Integration

#### Simple Integration

```html
<div id="newsletter-signup"></div>

<script>
// Your JavaScript snippet from the dashboard
</script>
```

#### Using Data Attributes (Hosted Script)

```html
<div id="newsletter-signup" 
     data-project-id="your-project-id"
     data-api-key="your-api-key"
     data-button-text="Subscribe Now"
     data-placeholder="Enter your email"
     data-success-message="Thanks for subscribing!">
</div>

<script src="https://your-domain.com/embed/newsletter.js"></script>
```

## Configuration

### Newsletter Settings

All newsletter-specific settings can be configured in `config/newsletter.php`:

- Double opt-in settings
- Rate limiting
- Email validation rules
- Analytics retention
- Export limits
- Security settings

### Email Templates

Customize email templates in:
- `resources/views/emails/confirmation.blade.php` - Confirmation email
- `resources/views/emails/welcome.blade.php` - Welcome email
- `resources/views/emails/admin-notification.blade.php` - Admin notifications

### Disposable Email Blocking

Configure blocked domains in:
- Environment: `NEWSLETTER_DISPOSABLE_DOMAINS_PATH`
- Config: `config/newsletter.php` - `disposable_domains` array

## Security

### CORS Configuration

Configure allowed origins per project in the dashboard. Use specific domains instead of `*` for production.

### Rate Limiting

Default rate limits:
- Subscriptions: 30 requests per minute
- Unsubscribe: 10 requests per minute
- General API: 60 requests per minute

### API Key Security

- API keys are generated using cryptographically secure random functions
- Keys can be regenerated at any time
- All API requests are logged for monitoring

### Privacy Features

- IP addresses are automatically masked in logs
- Unsubscribe always returns 200 OK (privacy-focused)
- Email addresses are hashed in admin notifications

## Monitoring and Analytics

### Dashboard Analytics

- Total subscriptions per project
- Growth trends and charts
- API request monitoring
- Geographic distribution (anonymized)

### Log Files

Important logs to monitor:
- `storage/logs/laravel.log` - Application logs
- `storage/logs/worker.log` - Queue worker logs

### Health Monitoring

Use the health endpoint for uptime monitoring:
```bash
curl https://your-domain.com/api/v1/health
```

## Maintenance

### Database Cleanup

```bash
# Clean up old API request logs (runs automatically via scheduler)
php artisan newsletter:cleanup

# Manual cleanup
php artisan newsletter:cleanup --days=30
```

### Backup

Regular backup recommendations:
- Database (daily)
- Environment file
- Storage directory (if using local file storage)

## Troubleshooting

### Common Issues

1. **CORS Errors**
   - Verify your domain is in the project's allowed origins
   - Check that the domain matches exactly (including www)

2. **Email Not Sending**
   - Verify SMTP configuration in `.env`
   - Check queue worker is running
   - Review `storage/logs/laravel.log`

3. **API Authentication Fails**
   - Verify `X-Project-ID` and `X-Api-Key` headers
   - Check project status is 'active'
   - Ensure API key hasn't been regenerated

4. **Rate Limiting**
   - Check rate limit configuration
   - Monitor API request logs
   - Consider increasing limits for high-traffic sites

### Debug Mode

For development, enable debug mode:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## API Reference

### Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/health` | Health check | No |
| POST | `/api/v1/subscriptions` | Subscribe email | Yes |
| POST | `/api/v1/unsubscribe` | Unsubscribe email | Yes |
| GET | `/confirm` | Confirm subscription | Signed URL |

### Authentication Headers

All protected endpoints require:
```
X-Project-ID: your-project-id
X-Api-Key: your-api-key
```

### Response Formats

#### Success Response
```json
{
  "success": true,
  "message": "Subscription successful",
  "data": {
    "email": "user@example.com",
    "status": "pending",
    "requires_confirmation": true
  }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions:
- Create an issue on GitHub
- Review the troubleshooting section
- Check Laravel documentation for framework-specific issues

---

Built with ❤️ using Laravel 11