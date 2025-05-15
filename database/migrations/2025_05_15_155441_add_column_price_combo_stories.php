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
        Schema::table('stories', function (Blueprint $table) {
            $table->integer('combo_price')->nullable()->default(0)->after('is_18_plus')->comment('Giá combo của truyện');
            $table->boolean('has_combo')->default(false)->after('combo_price')->comment('Trạng thái đã có combo hay chưa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('combo_price');
            $table->dropColumn('has_combo');
        });
    }
};
