<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class HideStory18PlusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::setConfig(
            'hide_story_18_plus',
            0,
            'Ẩn truyện 18+ ở trang chủ (1=ẩn, 0=hiện)'
        );

        Config::setConfig(
            'coin_bank_auto_percentage',
            20,
            'Phí nạp ngân hàng tự động (20%) nếu có nhập thì mới tính'
        );

        Config::setConfig(
            'min_bank_auto_deposit_amount',
            100000,
            'Số tiền tối thiểu có thể nạp ngân hàng tự động (100000 VNĐ)'
        );
    }
}


