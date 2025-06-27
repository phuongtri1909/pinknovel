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
        Schema::table('story_purchases', function (Blueprint $table) {
            $table->integer('amount_received')->after('amount_paid');
        });

        Schema::table('chapter_purchases', function (Blueprint $table) {
            $table->integer('amount_received')->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('story_purchases', function (Blueprint $table) {
            $table->dropColumn('amount_received');
        });

        Schema::table('chapter_purchases', function (Blueprint $table) {
            $table->dropColumn('amount_received');
        });
    }
};
