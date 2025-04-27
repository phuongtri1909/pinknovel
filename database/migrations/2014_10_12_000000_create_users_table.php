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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->enum('role', ['admin','mod','user'])->default('user');
            $table->enum('active', ['active', 'inactive'])->default('inactive');
            $table->string('key_active')->nullable();
            $table->string('key_reset_password')->nullable();
            $table->boolean('ban_login')->default(false);
            $table->boolean('ban_comment')->default(false);
            $table->boolean('ban_rate')->default(false);
            $table->boolean('ban_read')->default(false);
            $table->string('ip_address')->nullable();
            $table->timestamp('reset_password_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        }, 'ENGINE=InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
