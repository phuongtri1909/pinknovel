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
            [
                'key' => 'monopoly_author_percentage',
                'value' => '90', // 90% tác giả nhận được khi truyện được độc quyền
                'description' => '0% tác giả nhận được khi truyện được độc quyền',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'non_monopoly_author_percentage',
                'value' => '70', // 70% tác giả nhận được khi truyện không được độc quyền
                'description' => '70% tác giả nhận được khi truyện không được độc quyền',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'coin_paypal_rate',
                'value' => '10',
                'description' => 'Tỷ lệ chuyển đổi từ USD sang coin',
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
