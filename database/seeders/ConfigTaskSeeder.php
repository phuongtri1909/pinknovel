<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::setConfig(
            'daily_task_login_reward',
            1,
            'Số xu thưởng khi hoàn thành nhiệm vụ đăng nhập hàng ngày'
        );

        Config::setConfig(
            'daily_task_comment_reward',
            1,
            'Số xu thưởng khi hoàn thành nhiệm vụ bình luận truyện'
        );

        Config::setConfig(
            'daily_task_bookmark_reward',
            1,
            'Số xu thưởng khi hoàn thành nhiệm vụ theo dõi truyện'
        );

        Config::setConfig(
            'daily_task_share_reward',
            1,
            'Số xu thưởng khi hoàn thành nhiệm vụ chia sẻ truyện'
        );

        Config::setConfig(
            'story_featured_price',
            99999,
            'Số xu cần thiết để tác giả đề cử truyện lên trang chủ'
        );

        Config::setConfig(
            'story_featured_duration',
            1,
            'Số ngày truyện được đề cử (tính bằng ngày)'
        ); 
    }
} 