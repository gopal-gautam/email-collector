<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip_address', 45); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->string('path');
            $table->string('method', 10);
            $table->integer('status_code');
            $table->float('response_time')->nullable(); // in milliseconds
            $table->json('headers')->nullable();
            $table->timestamp('created_at');
            
            // Indexes for analytics and rate limiting
            $table->index(['project_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['created_at']);
            $table->index(['project_id', 'ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_requests');
    }
};
