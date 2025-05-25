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
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->foreignId('last_chapter_id')->nullable()->constrained('chapters')->onDelete('set null');
            $table->timestamp('last_read_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropForeign(['last_chapter_id']);
            $table->dropColumn(['last_chapter_id', 'last_read_at']);
        });
    }
};
