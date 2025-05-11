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
            $table->boolean('is_free')->default(true)->after('status');
            $table->integer('price')->nullable()->after('is_free');
            $table->string('password')->nullable()->after('price');
            $table->timestamp('scheduled_publish_at')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('is_free');
            $table->dropColumn('price');
            $table->dropColumn('password');
            $table->dropColumn('scheduled_publish_at');
        });
    }
};
