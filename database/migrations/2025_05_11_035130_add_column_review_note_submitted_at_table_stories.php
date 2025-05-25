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
            $table->text('review_note')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('review_note');
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('review_note');
            $table->dropColumn('submitted_at');
            $table->dropColumn('admin_note');
            $table->dropColumn('reviewed_at');
        });
    }
};