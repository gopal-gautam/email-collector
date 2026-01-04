<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ApiRequest extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'ip_address',
        'user_agent',
        'path',
        'method',
        'status_code',
        'response_time',
        'headers',
        'created_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'response_time' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiRequest) {
            if (!$apiRequest->created_at) {
                $apiRequest->created_at = now();
            }
        });
    }

    /**
     * Get the project that owns the API request
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Log an API request
     */
    public static function logRequest(
        ?int $projectId,
        string $ipAddress,
        ?string $userAgent,
        string $path,
        string $method,
        int $statusCode,
        ?float $responseTime = null,
        ?array $headers = null
    ): self {
        return static::create([
            'project_id' => $projectId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'path' => $path,
            'method' => strtoupper($method),
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'headers' => $headers,
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
     * Check if request was successful (2xx status)
     */
    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    /**
     * Check if request was a client error (4xx status)
     */
    public function isClientError(): bool
    {
        return $this->status_code >= 400 && $this->status_code < 500;
    }

    /**
     * Check if request was a server error (5xx status)
     */
    public function isServerError(): bool
    {
        return $this->status_code >= 500;
    }

    /**
     * Scope for successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('status_code', [200, 299]);
    }

    /**
     * Scope for failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    /**
     * Scope for specific project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for specific IP address
     */
    public function scopeForIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for specific path
     */
    public function scopeForPath($query, $path)
    {
        return $query->where('path', $path);
    }

    /**
     * Scope for specific method
     */
    public function scopeForMethod($query, $method)
    {
        return $query->where('method', strtoupper($method));
    }

    /**
     * Scope for recent requests
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for requests within date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for requests today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for requests this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for requests this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Get analytics data for a project
     */
    public static function getAnalyticsForProject(int $projectId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $requests = static::forProject($projectId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_requests' => $requests->count(),
            'successful_requests' => $requests->where('status_code', '>=', 200)->where('status_code', '<', 300)->count(),
            'failed_requests' => $requests->where('status_code', '>=', 400)->count(),
            'avg_response_time' => $requests->where('response_time', '>', 0)->avg('response_time'),
            'unique_ips' => $requests->pluck('ip_address')->unique()->count(),
            'top_paths' => $requests->groupBy('path')->map->count()->sortDesc()->take(10)->toArray(),
            'status_codes' => $requests->groupBy('status_code')->map->count()->sortDesc()->toArray(),
            'daily_stats' => static::getDailyStats($projectId, $days),
        ];
    }

    /**
     * Get daily statistics for a project
     */
    public static function getDailyStats(int $projectId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $dailyStats = static::forProject($projectId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as requests, AVG(CASE WHEN response_time > 0 THEN response_time END) as avg_response_time')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();

        // Fill in missing dates with zero values
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[$date] = $dailyStats[$date] ?? [
                'date' => $date,
                'requests' => 0,
                'avg_response_time' => null
            ];
        }

        return $result;
    }
}
