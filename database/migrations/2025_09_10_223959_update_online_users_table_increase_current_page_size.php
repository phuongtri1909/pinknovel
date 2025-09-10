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
        Schema::table('online_users', function (Blueprint $table) {
            $table->text('current_page')->nullable()->change();
            $table->text('referer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_users', function (Blueprint $table) {
            $table->string('current_page')->nullable()->change();
            $table->string('referer')->nullable()->change();
        });
    }
};
