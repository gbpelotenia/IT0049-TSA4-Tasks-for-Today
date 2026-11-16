<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        User::firstOrCreate(['email' => 'demo@example.com'], [
            'name' => 'Demo Member', 'password' => Hash::make('DemoPass123!'),
        ]);
        foreach ([
            ['title' => 'Prepare the Laravel project', 'description' => 'Review the project folders and install PHP dependencies.', 'priority' => 'high', 'status' => 'pending'],
            ['title' => 'Review the task board', 'description' => 'Check due dates and update the progress of current tasks.', 'priority' => 'medium', 'status' => 'in_progress'],
            ['title' => 'Write the setup guide', 'description' => 'Document local setup, mail configuration, and GitHub submission.', 'priority' => 'low', 'status' => 'done'],
        ] as $index => $data) {
            Task::firstOrCreate(['title' => $data['title']], $data + ['due_date' => today()->addDays($index)->toDateString()]);
        }
    }
}
