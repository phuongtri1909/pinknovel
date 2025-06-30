<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('card_deposits', function (Blueprint $table) {
            $table->decimal('penalty_amount', 15, 2)->nullable()->after('fee_amount')->comment('Số tiền phạt khi sai mệnh giá');
            $table->decimal('penalty_percent', 5, 2)->nullable()->after('penalty_amount')->comment('Phần trăm phạt khi sai mệnh giá');
        });
    }

    public function down()
    {
        Schema::table('card_deposits', function (Blueprint $table) {
            $table->dropColumn(['penalty_amount', 'penalty_percent']);
        });
    }
};