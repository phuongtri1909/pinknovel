<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('request_payment_paypals', function (Blueprint $table) {
            $table->decimal('base_usd_amount', 10, 2)->nullable()->default(0)->after('usd_amount');
            $table->enum('payment_method', ['friends_family', 'goods_services'])
                ->default('friends_family')->after('base_usd_amount');
        });

        DB::statement('UPDATE request_payment_paypals SET base_usd_amount = usd_amount');

        Schema::table('paypal_deposits', function (Blueprint $table) {
            $table->decimal('base_usd_amount', 10, 2)->nullable()->default(0)->after('usd_amount');
            $table->enum('payment_method', ['friends_family', 'goods_services'])
                ->default('friends_family')->after('base_usd_amount');
        });

        DB::statement('UPDATE paypal_deposits SET base_usd_amount = usd_amount');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_payment_paypals', function (Blueprint $table) {
            $table->dropColumn('base_usd_amount');
            $table->dropColumn('payment_method');
        });

        Schema::table('paypal_deposits', function (Blueprint $table) {
            $table->dropColumn('base_usd_amount');
            $table->dropColumn('payment_method');
        });
    }
};
