<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class WithdrawalConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Config::setConfig(
            'coin_bank_percentage',
            15,
            'Phí chuyển khoản ngân hàng (15%)'
        );

        Config::setConfig(
            'coin_exchange_rate',
            100,
            'quy đổi tiền việt sang xu 100 VND = 1 xu'
        );

        Config::setConfig(
            'coin_paypal_rate',
            20000,
            'quy đổi 1 đô sang bao nhiêu tiền việt'
        );

        Config::setConfig(
            'coin_paypal_percentage',
            0,
            'Phí nạp paypal (0%) nếu có nhập thì mới tính'
        );

        Config::setConfig(
            'coin_card_percentage',
            30,
            'Phí nạp thẻ (%)'
        );

        Config::setConfig(
            'withdrawal_coins_percentage',
            10,
            'Phí rút xu (%) áp dụng cho yêu cầu dưới mức ngưỡng (fee_threshold_amount)'
        );
        
        Config::setConfig(
            'min_withdrawal_amount',
            2000,
            'Số xu tối thiểu có thể rút sau khi đã quy đổi từ coin_exchange_rate'
        );
        
        Config::setConfig(
            'fee_threshold_amount',
            10000,
            'Ngưỡng số xu được miễn phí rút sau khi đã quy đổi từ coin_exchange_rate'
        );

        Config::setConfig(
            'monopoly_author_percentage',
            90,
            'Phần trăm tác giả nhận được khi truyện được độc quyền 90%'
        );

        Config::setConfig(
            'non_monopoly_author_percentage',
            70,
            'Phần trăm tác giả nhận được khi truyện không được độc quyền 70%'
        );
        
       
    }
} 