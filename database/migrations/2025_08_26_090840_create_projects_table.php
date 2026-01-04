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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->ulid('public_id')->unique();
            $table->string('api_key', 64)->unique();
            $table->json('allowed_origins')->nullable();
            $table->boolean('double_opt_in')->default(false);
            $table->boolean('welcome_email')->default(false);
            $table->boolean('admin_notifications')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('api_key');
            $table->index('public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
