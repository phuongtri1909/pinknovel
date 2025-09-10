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
        Schema::create('online_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID người dùng (null nếu guest)');
            $table->string('session_id')->comment('Session ID');
            $table->string('ip_address')->comment('Địa chỉ IP');
            $table->string('user_agent')->nullable()->comment('User Agent');
            $table->string('current_page')->nullable()->comment('Trang hiện tại');
            $table->string('referer')->nullable()->comment('Trang trước đó');
            $table->timestamp('last_activity')->comment('Hoạt động cuối cùng');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['session_id', 'last_activity']);
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_users');
    }
};
