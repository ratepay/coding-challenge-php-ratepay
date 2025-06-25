<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a test user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create sample tasks
        $sampleTasks = [
            [
                'title' => 'Complete project documentation',
                'description' => 'Write comprehensive documentation for the API project',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDays(3),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Review code changes',
                'description' => 'Review pull requests and provide feedback',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(1),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Setup testing environment',
                'description' => 'Configure automated testing pipeline',
                'status' => 'completed',
                'priority' => 'low',
                'due_date' => now()->subDays(2),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Database optimization',
                'description' => 'Optimize database queries for better performance',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addWeek(),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Security audit',
                'description' => 'Conduct security review of the application',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->addDays(5),
                'user_id' => $user->id,
            ],
        ];

        foreach ($sampleTasks as $task) {
            Task::create($task);
        }
    }
}
