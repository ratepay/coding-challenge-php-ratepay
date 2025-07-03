<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected User $otherUser;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users and authenticate
        $this->user = User::factory()->create([
            'email' => 'task-controller-test@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $this->otherUser = User::factory()->create([
            'email' => 'other-user-task@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test deleting own task via main task endpoint
     */
    public function test_can_delete_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Task successfully deleted']);

        // Verify task is actually deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test deleting another user's task returns 404
     */
    public function test_cannot_delete_another_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);

        // Verify task is not deleted
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /**
     * Test deleting non-existent task returns 404
     */
    public function test_deleting_nonexistent_task_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/tasks/99999");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test unauthorized access without token
     */
    public function test_unauthorized_delete_returns_401(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(401);

        // Verify task is not deleted
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /**
     * Test replacing own task via main task endpoint
     */
    public function test_can_replace_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $replaceData = [
            'data' => [
                'attributes' => [
                    'title' => 'Updated Task Title',
                    'description' => 'Updated task description',
                    'status' => 'completed',
                    'priority' => 'high',
                    'due_date' => '2024-12-31',
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'id' => $this->user->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/tasks/{$task->id}", $replaceData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'title' => 'Updated Task Title',
                    'status' => 'completed',
                    'priority' => 'high',
                ],
            ],
        ]);

        // Verify task is actually updated in database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'description' => 'Updated task description',
            'status' => 'completed',
            'priority' => 'high',
        ]);
    }

    /**
     * Test replacing another user's task returns 404
     */
    public function test_cannot_replace_another_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $replaceData = [
            'data' => [
                'attributes' => [
                    'title' => 'Updated Task Title',
                    'description' => 'Updated task description',
                    'status' => 'completed',
                    'priority' => 'high',
                    'due_date' => '2024-12-31',
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'id' => $this->otherUser->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/tasks/{$task->id}", $replaceData);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test replacing non-existent task returns 404
     */
    public function test_replacing_nonexistent_task_returns_404(): void
    {
        $replaceData = [
            'data' => [
                'attributes' => [
                    'title' => 'Updated Task Title',
                    'description' => 'Updated task description',
                    'status' => 'completed',
                    'priority' => 'high',
                    'due_date' => '2024-12-31',
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'id' => $this->user->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/tasks/99999", $replaceData);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test unauthorized replace access without token
     */
    public function test_unauthorized_replace_returns_401(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $replaceData = [
            'data' => [
                'attributes' => [
                    'title' => 'Updated Task Title',
                    'description' => 'Updated task description',
                    'status' => 'completed',
                    'priority' => 'high',
                    'due_date' => '2024-12-31',
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'id' => $this->user->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->putJson("/api/v1/tasks/{$task->id}", $replaceData);

        $response->assertStatus(401);
    }
} 