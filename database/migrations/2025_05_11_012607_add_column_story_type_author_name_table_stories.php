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
        Schema::table('stories', function (Blueprint $table) {
            $table->string('story_type')->default('original')->comment('original, collected,translated');
            $table->string('author_name')->nullable();
            $table->boolean('is_18_plus')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('story_type');
            $table->dropColumn('author_name');
            $table->dropColumn('is_18_plus');
        });
    }
};
