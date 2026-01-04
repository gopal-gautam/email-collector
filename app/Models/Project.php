<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'slug',
        'status',
        'public_id',
        'api_key',
        'allowed_origins',
        'double_opt_in',
        'welcome_email',
        'admin_notifications',
    ];

    protected $casts = [
        'allowed_origins' => 'array',
        'double_opt_in' => 'boolean',
        'welcome_email' => 'boolean',
        'admin_notifications' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (!$project->public_id) {
                $project->public_id = (string) new Ulid();
            }
            if (!$project->api_key) {
                $project->api_key = $project->generateApiKey();
            }
            if (!$project->slug) {
                $project->slug = Str::slug($project->name) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Generate a secure API key
     */
    public function generateApiKey(): string
    {
        do {
            $apiKey = 'nlc_' . Str::random(56); // Newsletter Collector prefix + 56 random chars
        } while (static::where('api_key', $apiKey)->exists());

        return $apiKey;
    }

    /**
     * Regenerate the API key
     */
    public function regenerateApiKey(): string
    {
        $this->api_key = $this->generateApiKey();
        $this->save();
        return $this->api_key;
    }

    /**
     * Check if the project is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if an origin is allowed for CORS
     */
    public function isOriginAllowed(string $origin): bool
    {
        if (!$this->allowed_origins) {
            return false;
        }

        // Ensure allowed_origins is an array
        $allowedOrigins = $this->allowed_origins;
        if (is_string($allowedOrigins)) {
            $allowedOrigins = json_decode($allowedOrigins, true);
        }
        
        if (!is_array($allowedOrigins)) {
            return false;
        }

        // Normalize origin to lowercase for case-insensitive comparison
        $normalizedOrigin = strtolower($origin);

        foreach ($allowedOrigins as $allowedOrigin) {
            // Normalize allowed origin to lowercase
            $normalizedAllowedOrigin = strtolower($allowedOrigin);
            
            if ($normalizedAllowedOrigin === '*' || $normalizedAllowedOrigin === $normalizedOrigin) {
                return true;
            }
            
            // Support wildcard subdomains like *.example.com
            if (str_contains($normalizedAllowedOrigin, '*')) {
                $pattern = str_replace('*', '.*', preg_quote($normalizedAllowedOrigin, '/'));
                if (preg_match('/^' . $pattern . '$/', $normalizedOrigin)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the user that owns the project
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscriptions for the project
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the API requests for the project
     */
    public function apiRequests(): HasMany
    {
        return $this->hasMany(ApiRequest::class);
    }

    /**
     * Get subscribed emails count
     */
    public function getSubscribedCountAttribute(): int
    {
        return $this->subscriptions()->where('status', 'subscribed')->count();
    }

    /**
     * Get pending emails count
     */
    public function getPendingCountAttribute(): int
    {
        return $this->subscriptions()->where('status', 'pending')->count();
    }

    /**
     * Get total subscriptions count
     */
    public function getTotalSubscriptionsAttribute(): int
    {
        return $this->subscriptions()->count();
    }

    /**
     * Scope to get active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get projects by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
