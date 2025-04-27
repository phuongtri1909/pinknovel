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
        Schema::table('chapters', function (Blueprint $table) {
            // Drop the unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add a composite unique constraint on story_id and slug
            $table->unique(['story_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['story_id', 'slug']);
            
            // Add back the unique constraint on slug
            $table->unique(['slug']);
        });
    }
};