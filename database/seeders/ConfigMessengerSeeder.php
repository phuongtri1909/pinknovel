<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigMessengerSeeder extends Seeder
{
    /**
     * Seed cấu hình Facebook Messenger chat button.
     */
    public function run(): void
    {
        Config::setConfig(
            'messenger_chat_url',
            'https://m.me/YourFanpageUsername',
            'Link Messenger chat đến fanpage (dạng https://m.me/username hoặc https://www.facebook.com/messages/t/pageID)'
        );

        Config::setConfig(
            'messenger_chat_enabled',
            1,
            'Bật/tắt nút chat Messenger trên trang (1 = bật, 0 = tắt)'
        );
    }
}
