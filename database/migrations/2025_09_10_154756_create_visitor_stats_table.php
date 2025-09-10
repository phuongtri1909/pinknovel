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
        Schema::create('visitor_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('Ngày thống kê');
            $table->integer('total_visits')->default(0)->comment('Tổng lượt truy cập');
            $table->integer('unique_visitors')->default(0)->comment('Số người dùng duy nhất');
            $table->integer('page_views')->default(0)->comment('Tổng lượt xem trang');
            $table->integer('new_users')->default(0)->comment('Số người dùng mới');
            $table->integer('returning_users')->default(0)->comment('Số người dùng quay lại');
            $table->json('hourly_stats')->nullable()->comment('Thống kê theo giờ');
            $table->json('page_stats')->nullable()->comment('Thống kê trang được xem nhiều nhất');
            $table->timestamps();
            
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_stats');
    }
};
