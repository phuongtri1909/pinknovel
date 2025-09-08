<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('11111111'),
                'role' => 'admin',
                'active' => 'active',
            ]);
        }
        
        $this->call([
            WithdrawalConfigSeeder::class,
            ConfigTaskSeeder::class,
            DailyTaskSeeder::class,
        ]);
    }
}
