<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default config values
        DB::table('configs')->insert([
            [
                'key' => 'bank_transfer_discount',
                'value' => '0', // 0% discount by default
                'description' => 'Discount percentage for bank transfer payment method',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'card_payment_discount',
                'value' => '10', // 10% discount by default
                'description' => 'Discount percentage for card payment method',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'coin_exchange_rate',
                'value' => '1000', // 1000 VND = 1 coin by default
                'description' => 'Exchange rate for VND to coins',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
