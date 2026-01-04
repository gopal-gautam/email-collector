<?php

use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UnsubscribeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API v1 routes (no authentication required for health check)
Route::prefix('v1')->group(function () {
    // Health check endpoint - no authentication required
    Route::get('/health', function () {
        return response()->json(['ok' => true]);
    })->name('api.health');
    
    // CORS preflight handling for all API endpoints
    Route::options('/{any}', function () {
        return response('', 204);
    })->where('any', '.*')
      ->middleware(['project.auth', 'project.cors'])
      ->name('api.options');
    
    // Protected API endpoints requiring project authentication
    Route::middleware(['project.auth', 'project.cors'])->group(function () {
        
        // Subscription management
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('api.subscriptions.store');
            
        Route::post('/unsubscribe', [UnsubscribeController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('api.unsubscribe.store');
    });
});