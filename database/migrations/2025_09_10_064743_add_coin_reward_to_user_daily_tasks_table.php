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
        Schema::table('user_daily_tasks', function (Blueprint $table) {
            $table->integer('coin_reward')->default(0)->after('completed_count')->comment('Số xu thưởng tại thời điểm hoàn thành nhiệm vụ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_daily_tasks', function (Blueprint $table) {
            $table->dropColumn('coin_reward');
        });
    }
};
