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
        Schema::create('author_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('facebook_link')->comment('Facebook profile link');
            $table->string('telegram_link')->nullable()->comment('Telegram username or link');
            $table->string('other_platform')->nullable()->comment('Other platform name');
            $table->string('other_platform_link')->nullable()->comment('Other platform link or username');
            $table->text('introduction')->nullable()->comment('Author self-introduction');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable()->comment('Admin feedback on application');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_applications');
    }
};
