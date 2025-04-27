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
        Schema::table('banners', function (Blueprint $table) {
            $table->text('link_aff')->nullable()->after('link');
        });
        
        Schema::table('stories', function (Blueprint $table) {
            $table->text('link_aff')->nullable()->after('slug');
        });
        
        Schema::table('chapters', function (Blueprint $table) {
            $table->text('link_aff')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('link_aff');
        });
        
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('link_aff');
        });
        
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('link_aff');
        });
    }
};