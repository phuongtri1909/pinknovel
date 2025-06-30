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
        Schema::create('story_transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('story_id');
            $table->string('story_title');
            $table->string('story_slug')->nullable();
            
            // Old author info
            $table->unsignedBigInteger('old_author_id');
            $table->string('old_author_name');
            $table->string('old_author_email');
            
            // New author info
            $table->unsignedBigInteger('new_author_id');
            $table->string('new_author_name');
            $table->string('new_author_email');
            
            // Transfer details
            $table->text('reason');
            $table->enum('transfer_type', ['single', 'bulk'])->default('single');
            $table->json('transfer_metadata')->nullable(); // Store additional data like chapter count, etc.
            
            // Admin who performed the transfer
            $table->unsignedBigInteger('transferred_by');
            $table->string('transferred_by_name');
            
            // Technical details
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('transferred_at');
            
            // Status
            $table->enum('status', ['completed', 'failed', 'reverted'])->default('completed');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['story_id', 'transferred_at']);
            $table->index(['old_author_id', 'transferred_at']);
            $table->index(['new_author_id', 'transferred_at']);
            $table->index(['transferred_by', 'transferred_at']);
            $table->index('transferred_at');
            $table->index('transfer_type');
            $table->index('status');
            
            // Foreign keys
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
            $table->foreign('old_author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('new_author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transferred_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_transfer_histories');
    }
};
