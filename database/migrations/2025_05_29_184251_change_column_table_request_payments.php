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
        Schema::table('request_payments', function (Blueprint $table) {
            $table->dropColumn('base_coins');
            $table->dropColumn('bonus_coins');
            $table->dropColumn('total_coins');
            $table->dropColumn('discount');

            $table->unsignedInteger('coins')->comment('số lượng xu sau khi trừ phí');
            $table->unsignedInteger('fee')->comment('phí giao dịch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_payments', function (Blueprint $table) {
            $table->unsignedInteger('base_coins')->comment('số lượng xu sau khi trừ phí');
            $table->unsignedInteger('bonus_coins')->comment('số lượng xu thưởng');
            $table->unsignedInteger('total_coins')->comment('tổng số lượng xu');
            $table->unsignedInteger('fee')->comment('phí giao dịch');

            $table->dropColumn('coins');
            $table->dropColumn('fee');
        });
    }
};
