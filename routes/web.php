<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\ConfirmSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route for newsletter functionality
Route::get('/test-newsletter', function () {
    return view('test-newsletter');
})->name('test-newsletter');

// Simple test route
Route::get('/simple-test', function () {
    return view('simple-test');
})->name('simple-test');

// Basic test route
Route::get('/basic-test', function () {
    return view('basic-test');
})->name('basic-test');

// Debug test route
Route::get('/debug-test', function () {
    return view('debug-test');
})->name('debug-test');

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public confirmation route (signed URLs)
Route::get('/confirm', ConfirmSubscriptionController::class)
    ->middleware('signed')
    ->name('confirm-subscription');

// Embed script endpoint
Route::get('/embed/newsletter.js', [EmbedController::class, 'script'])
    ->name('embed.newsletter');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Project management routes
    Route::resource('projects', ProjectController::class);
    
    // Additional project routes
    Route::get('/projects/{project}/snippet', [ProjectController::class, 'snippet'])
        ->name('projects.snippet');
    Route::get('/projects/{project}/analytics', [ProjectController::class, 'analytics'])
        ->name('projects.analytics');
    Route::get('/projects/{project}/subscriptions', [ProjectController::class, 'subscriptions'])
        ->name('projects.subscriptions');
    Route::get('/projects/{project}/export', [ProjectController::class, 'export'])
        ->name('projects.export');
    Route::post('/projects/{project}/regenerate-api-key', [ProjectController::class, 'regenerateApiKey'])
        ->name('projects.regenerate-api-key');
});

require __DIR__.'/auth.php';
