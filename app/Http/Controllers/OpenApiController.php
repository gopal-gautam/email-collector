<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Newsletter Collector API",
 *     version="1.0.0",
 *     description="A comprehensive API for collecting and managing newsletter subscriptions with project-based authentication, CORS support, and email management.",
 *     @OA\Contact(
 *         email="admin@newsletter-collector.com",
 *         name="Newsletter Collector Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Newsletter Collector API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="ProjectAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-Project-ID",
 *     description="Project Public ID (ULID format)"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-Api-Key",
 *     description="Project API Key"
 * )
 * 
 * @OA\Tag(
 *     name="Health",
 *     description="API health and status endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="Newsletter subscription management"
 * )
 * 
 * @OA\Tag(
 *     name="Unsubscribe", 
 *     description="Newsletter unsubscribe operations"
 * )
 * 
 * @OA\Tag(
 *     name="Confirmation",
 *     description="Email confirmation operations"
 * )
 * 
 * @OA\Schema(
 *     schema="Subscription",
 *     type="object",
 *     required={"id", "email", "status", "created_at"},
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="status", type="string", enum={"pending", "subscribed", "unsubscribed", "bounced"}, example="subscribed"),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="source_url", type="string", example="https://example.com/signup"),
 *     @OA\Property(property="referrer_url", type="string", example="https://google.com"),
 *     @OA\Property(property="meta", type="object", example={"campaign": "summer2024"}),
 *     @OA\Property(property="confirmed_at", type="string", format="datetime", nullable=true),
 *     @OA\Property(property="unsubscribed_at", type="string", format="datetime", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 * 
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     required={"public_id", "name", "status"},
 *     @OA\Property(property="public_id", type="string", example="01ARZ3NDEKTSV4RRFFQ69G5FAV"),
 *     @OA\Property(property="name", type="string", example="My Newsletter Project"),
 *     @OA\Property(property="description", type="string", example="Newsletter for my awesome project"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="double_opt_in", type="boolean", example=true),
 *     @OA\Property(property="welcome_email", type="boolean", example=true),
 *     @OA\Property(property="admin_notifications", type="boolean", example=false),
 *     @OA\Property(property="allowed_origins", type="array", @OA\Items(type="string"), example={"https://example.com", "https://www.example.com"}),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     required={"status"},
 *     @OA\Property(property="status", type="string", example="ok"),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     required={"error"},
 *     @OA\Property(property="error", type="string", example="Validation failed"),
 *     @OA\Property(property="message", type="string", example="The provided data is invalid"),
 *     @OA\Property(property="errors", type="object")
 * )
 * 
 * @OA\Schema(
 *     schema="HealthResponse",
 *     type="object",
 *     required={"ok"},
 *     @OA\Property(property="ok", type="boolean", example=true),
 *     @OA\Property(property="timestamp", type="string", format="datetime", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="version", type="string", example="1.0.0")
 * )
 * 
 * @OA\Parameter(
 *     parameter="ProjectIdHeader",
 *     name="X-Project-ID",
 *     in="header",
 *     required=true,
 *     @OA\Schema(type="string"),
 *     description="Project Public ID (ULID format)"
 * )
 * 
 * @OA\Parameter(
 *     parameter="ApiKeyHeader",
 *     name="X-Api-Key", 
 *     in="header",
 *     required=true,
 *     @OA\Schema(type="string"),
 *     description="Project API Key"
 * )
 * 
 * @OA\Parameter(
 *     parameter="OriginHeader",
 *     name="Origin",
 *     in="header",
 *     required=false,
 *     @OA\Schema(type="string"),
 *     description="Request origin (for CORS validation)",
 *     example="https://example.com"
 * )
 */
class OpenApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/health",
     *     tags={"Health"},
     *     summary="Health check",
     *     description="Check API health and status",
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(ref="#/components/schemas/HealthResponse")
     *     )
     * )
     */
    public function health()
    {
        // This method is just for documentation
        // Actual implementation is in the routes file
    }
    
    /**
     * General API Information
     * 
     * This API provides comprehensive newsletter subscription management with the following features:
     * 
     * ## Authentication
     * All protected endpoints require two headers:
     * - `X-Project-ID`: Your project's public identifier (ULID format)
     * - `X-Api-Key`: Your project's API key
     * 
     * ## Rate Limiting  
     * - Subscriptions: 30 requests per minute per IP
     * - Unsubscribe: 10 requests per minute per IP
     * - General API: 60 requests per minute per IP
     * 
     * ## CORS Support
     * The API supports Cross-Origin Resource Sharing (CORS) with per-project origin configuration.
     * Make sure your domain is added to your project's allowed origins list.
     * 
     * ## Email Management
     * - Double opt-in confirmation (configurable per project)
     * - Welcome emails (configurable per project) 
     * - Admin notifications (configurable per project)
     * - Privacy-focused unsubscribe (always returns 200 OK)
     * 
     * ## Error Handling
     * The API uses standard HTTP status codes:
     * - 200: Success
     * - 401: Authentication failed
     * - 403: Access denied (CORS)
     * - 422: Validation error
     * - 429: Rate limit exceeded
     * - 500: Server error
     * 
     * ## Privacy and Security
     * - IP addresses are masked in logs
     * - Email addresses are hashed in admin notifications
     * - Unsubscribe always returns success (doesn't leak subscription status)
     * - Request logging for analytics and abuse prevention
     * 
     * ## JavaScript Integration
     * Use our hosted JavaScript snippet or generate your own custom implementation.
     * See the dashboard for copy-paste code examples.
     */
}