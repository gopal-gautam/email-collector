<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'email',
        'status',
        'ip_address',
        'user_agent',
        'referrer',
        'source_url',
        'meta',
        'confirmed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'confirmed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_BOUNCED = 'bounced';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            // Normalize email to lowercase
            $subscription->email = strtolower(trim($subscription->email));
        });

        static::updating(function ($subscription) {
            // Normalize email to lowercase
            $subscription->email = strtolower(trim($subscription->email));
            
            // Set confirmed_at when status changes to subscribed
            if ($subscription->isDirty('status') && $subscription->status === self::STATUS_SUBSCRIBED && !$subscription->confirmed_at) {
                $subscription->confirmed_at = now();
            }
        });
    }

    /**
     * Get the project that owns the subscription
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if subscription is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if subscription is subscribed
     */
    public function isSubscribed(): bool
    {
        return $this->status === self::STATUS_SUBSCRIBED;
    }

    /**
     * Check if subscription is unsubscribed
     */
    public function isUnsubscribed(): bool
    {
        return $this->status === self::STATUS_UNSUBSCRIBED;
    }

    /**
     * Check if subscription is bounced
     */
    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    /**
     * Subscribe the email
     */
    public function subscribe(): bool
    {
        $this->status = self::STATUS_SUBSCRIBED;
        $this->confirmed_at = now();
        return $this->save();
    }

    /**
     * Unsubscribe the email
     */
    public function unsubscribe(): bool
    {
        $this->status = self::STATUS_UNSUBSCRIBED;
        return $this->save();
    }

    /**
     * Mark as bounced
     */
    public function markAsBounced(): bool
    {
        $this->status = self::STATUS_BOUNCED;
        return $this->save();
    }

    /**
     * Generate confirmation URL for double opt-in
     */
    public function generateConfirmationUrl(): string
    {
        $expiryHours = (int) config('newsletter.confirmation_expiry_hours', 48);
        $expiresAt = now()->addHours($expiryHours);
        
        return URL::temporarySignedRoute(
            'confirm-subscription',
            $expiresAt,
            ['subscription' => $this->id]
        );
    }

    /**
     * Alias for generateConfirmationUrl for backward compatibility
     */
    public function getConfirmationUrl(): string
    {
        return $this->generateConfirmationUrl();
    }

    /**
     * Generate unsubscribe URL
     */
    public function generateUnsubscribeUrl(): string
    {
        return URL::signedRoute('unsubscribe', [
            'project' => $this->project->public_id,
            'email' => $this->email
        ]);
    }

    /**
     * Get masked IP address for privacy
     */
    public function getMaskedIpAttribute(): string
    {
        if (!$this->ip_address) {
            return 'Unknown';
        }

        // IPv4 masking
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $this->ip_address);
            return $parts[0] . '.' . $parts[1] . '.***.' . $parts[3];
        }

        // IPv6 masking  
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $this->ip_address);
            return implode(':', array_slice($parts, 0, 3)) . ':***';
        }

        return 'Unknown';
    }

    /**
     * Validate email format and check for disposable domains
     */
    public static function validateEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $errors = [];

        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
            return ['valid' => false, 'errors' => $errors, 'email' => $email];
        }

        // RFC compliant regex validation
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (!preg_match($pattern, $email)) {
            $errors[] = 'Email does not meet RFC standards';
        }

        // Check for disposable domains (placeholder)
        $domain = substr(strrchr($email, '@'), 1);
        $disposableDomains = config('newsletter.disposable_domains', []);
        if (in_array($domain, $disposableDomains)) {
            $errors[] = 'Disposable email domains are not allowed';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'email' => $email
        ];
    }

    /**
     * Scope for subscribed emails
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', self::STATUS_SUBSCRIBED);
    }

    /**
     * Scope for pending emails
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for unsubscribed emails
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('status', self::STATUS_UNSUBSCRIBED);
    }

    /**
     * Scope for bounced emails
     */
    public function scopeBounced($query)
    {
        return $query->where('status', self::STATUS_BOUNCED);
    }

    /**
     * Scope for project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for recent subscriptions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
