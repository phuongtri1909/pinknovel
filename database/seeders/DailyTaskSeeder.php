<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyTask;

class DailyTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating default daily tasks...');
        
        DailyTask::createDefaultTasks();
        
        $this->command->info('Default daily tasks created successfully!');
        
        // Show created tasks
        $tasks = DailyTask::active()->ordered()->get();
        $this->command->table(
            ['ID', 'Name', 'Type', 'Coin Reward', 'Max Per Day'],
            $tasks->map(function ($task) {
                return [
                    $task->id,
                    $task->name,
                    $task->type,
                    $task->coin_reward,
                    $task->max_per_day,
                ];
            })
        );
    }
}