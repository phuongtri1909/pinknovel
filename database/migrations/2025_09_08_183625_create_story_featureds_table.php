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
        Schema::create('story_featureds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['admin', 'author'])->default('author');
            $table->integer('featured_order')->nullable(); // Chỉ dành cho admin
            $table->integer('price_paid')->default(0); // Số xu đã trả (chỉ dành cho author)
            $table->integer('duration_days')->default(1); // Số ngày đề cử
            $table->timestamp('featured_at');
            $table->timestamp('featured_until');
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['story_id', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index('featured_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_featureds');
    }
};
