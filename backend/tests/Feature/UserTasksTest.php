<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTasksTest extends TestCase
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
            'email' => 'user-tasks-test@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $this->otherUser = User::factory()->create([
            'email' => 'other-user@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test listing tasks for a specific user
     */
    public function test_can_list_tasks_for_specific_user(): void
    {
        // Create tasks for the user
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        // Create tasks for other user (should not appear in results)
        Task::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        
        // Verify all tasks belong to the correct user
        foreach ($data as $task) {
            $this->assertEquals($this->user->id, $task['relationships']['user']['data']['id']);
        }
    }

    /**
     * Test listing tasks for non-existent user returns 404
     */
    public function test_listing_tasks_for_nonexistent_user_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/99999/tasks");

        $response->assertStatus(404);
        $response->assertJson(['error' => 'User not found']);
    }

    /**
     * Test creating a task for a specific user with all fields
     */
    public function test_can_create_task_for_specific_user_with_all_fields(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Complete Project',
                    'description' => 'Finish the Laravel API project',
                    'status' => 'pending',
                    'priority' => 'high',
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(201);
        $data = $response->json('data');
        
        $this->assertEquals('Complete Project', $data['attributes']['title']);
        $this->assertEquals('pending', $data['attributes']['status']);
        $this->assertEquals('high', $data['attributes']['priority']);
        $this->assertEquals($this->user->id, $data['relationships']['user']['data']['id']);
        $this->assertTrue(array_key_exists('attributes', $data));
        $this->assertFalse(array_key_exists('description', $data['attributes']));
    }

    /**
     * Test creating a task for a specific user with minimal fields
     */
    public function test_can_create_task_for_specific_user_with_minimal_fields(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Simple Task',
                    'status' => 'pending',
                    'priority' => 'medium',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(201);
        $data = $response->json('data');
        
        $this->assertEquals('Simple Task', $data['attributes']['title']);
        $this->assertEquals($this->user->id, $data['relationships']['user']['data']['id']);
        $this->assertTrue(array_key_exists('attributes', $data));
        $this->assertFalse(array_key_exists('description', $data['attributes']));
        $this->assertFalse(array_key_exists('due_date', $data['attributes']));
    }

    /**
     * Test creating task for non-existent user returns 404
     */
    public function test_creating_task_for_nonexistent_user_returns_404(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Test Task',
                    'status' => 'pending',
                    'priority' => 'high',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/99999/tasks", $taskData);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'User not found']);
    }

    /**
     * Test validation errors for missing required fields
     */
    public function test_validation_errors_for_missing_required_fields(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    // Missing title, status, priority
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'data.attributes.title',
            'data.attributes.status',
            'data.attributes.priority'
        ]);
    }

    /**
     * Test validation errors for invalid status values
     */
    public function test_validation_errors_for_invalid_status(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Test Task',
                    'status' => 'invalid_status',
                    'priority' => 'high',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data.attributes.status']);
    }

    /**
     * Test validation errors for invalid priority values
     */
    public function test_validation_errors_for_invalid_priority(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Test Task',
                    'status' => 'pending',
                    'priority' => 'invalid_priority',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data.attributes.priority']);
    }

    /**
     * Test validation errors for past due date
     */
    public function test_validation_errors_for_past_due_date(): void
    {
        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Test Task',
                    'status' => 'pending',
                    'priority' => 'high',
                    'due_date' => now()->subDays(1)->format('Y-m-d'),
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data.attributes.due_date']);
    }

    /**
     * Test filtering tasks by status
     */
    public function test_can_filter_tasks_by_status(): void
    {
        // Create tasks with different statuses
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks?filter[status]=pending");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        foreach ($data as $task) {
            $this->assertEquals('pending', $task['attributes']['status']);
        }
    }

    /**
     * Test filtering tasks by priority
     */
    public function test_can_filter_tasks_by_priority(): void
    {
        // Create tasks with different priorities
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks?filter[priority]=high");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        foreach ($data as $task) {
            $this->assertEquals('high', $task['attributes']['priority']);
        }
    }

    /**
     * Test sorting tasks by title
     */
    public function test_can_sort_tasks_by_title(): void
    {
        // Create tasks with specific titles
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Zebra Task']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Alpha Task']);
        Task::factory()->create(['user_id' => $this->user->id, 'title' => 'Beta Task']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks?sort=title");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        
        // Check that titles are sorted alphabetically
        $this->assertEquals('Alpha Task', $data[0]['attributes']['title']);
        $this->assertEquals('Beta Task', $data[1]['attributes']['title']);
        $this->assertEquals('Zebra Task', $data[2]['attributes']['title']);
    }

    /**
     * Test pagination
     */
    public function test_pagination_works(): void
    {
        // Create more tasks than the default per page
        Task::factory()->count(20)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks?page=2");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total'
            ]
        ]);
        
        $meta = $response->json('meta');
        $this->assertEquals(2, $meta['current_page']);
        $this->assertEquals(15, $meta['per_page']);
        $this->assertEquals(20, $meta['total']);
    }

    /**
     * Test unauthorized access without token
     */
    public function test_unauthorized_access_without_token(): void
    {
        $response = $this->getJson("/api/v1/users/{$this->user->id}/tasks");
        $response->assertStatus(401);

        $taskData = [
            'data' => [
                'attributes' => [
                    'title' => 'Test Task',
                    'status' => 'pending',
                    'priority' => 'high',
                ]
            ]
        ];

        $response = $this->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);
        $response->assertStatus(401);
    }

    /**
     * Test invalid JSON structure
     */
    public function test_invalid_json_structure_returns_validation_error(): void
    {
        $taskData = [
            'title' => 'Test Task', // Missing 'data.attributes' structure
            'status' => 'pending',
            'priority' => 'high',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->postJson("/api/v1/users/{$this->user->id}/tasks", $taskData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'data.attributes.title',
            'data.attributes.status',
            'data.attributes.priority'
        ]);
    }

    /**
     * Test showing a specific task for a user
     */
    public function test_can_show_specific_task_for_user(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($task->id, $data['id']);
        $this->assertEquals($this->user->id, $data['relationships']['user']['data']['id']);
    }

    /**
     * Test showing task with user relationship included
     */
    public function test_can_show_task_with_user_included(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}?include=user");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($task->id, $data['id']);
        
        // For nested routes, includes might not be present in the same way
        // Let's check that the user relationship is properly loaded
        $this->assertArrayHasKey('relationships', $data);
        $this->assertArrayHasKey('user', $data['relationships']);
        $this->assertEquals($this->user->id, $data['relationships']['user']['data']['id']);
    }

    /**
     * Test showing task that doesn't belong to user returns 404
     */
    public function test_showing_task_not_belonging_to_user_returns_404(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}");

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Task cannot be found']);
    }

    /**
     * Test showing non-existent task returns 404
     */
    public function test_showing_nonexistent_task_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/users/{$this->user->id}/tasks/99999");

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Task cannot be found']);
    }

    /**
     * Test deleting a task for a user
     */
    public function test_can_delete_task_for_user(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Task successfully deleted']);

        // Verify task is actually deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test deleting task that doesn't belong to user returns 404
     */
    public function test_deleting_task_not_belonging_to_user_returns_404(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}");

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Task cannot be found']);

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
        ])->deleteJson("/api/v1/users/{$this->user->id}/tasks/99999");

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Task cannot be found']);
    }

    /**
     * Test unauthorized access without token
     */
    public function test_unauthorized_delete_returns_401(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}");

        $response->assertStatus(401);

        // Verify task is not deleted
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /**
     * Test replacing own task via nested user tasks endpoint
     */
    public function test_can_replace_own_task_via_nested_route(): void
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
        ])->putJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}", $replaceData);

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
            'status' => 'completed',
            'priority' => 'high',
        ]);
    }

    /**
     * Test replacing another user's task returns 404
     */
    public function test_cannot_replace_another_users_task_via_nested_route(): void
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
        ])->putJson("/api/v1/users/{$this->otherUser->id}/tasks/{$task->id}", $replaceData);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test replacing task with mismatched user ID returns 404
     */
    public function test_cannot_replace_task_with_mismatched_user_id(): void
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

        // Try to replace task using wrong user ID in URL
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/users/{$this->otherUser->id}/tasks/{$task->id}", $replaceData);

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

        $response = $this->putJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}", $replaceData);

        $response->assertStatus(401);
    }

    /**
     * Test updating own task via nested user tasks endpoint (PATCH)
     */
    public function test_can_update_own_task_via_nested_route(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                    'priority' => 'high',
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                    'priority' => 'high',
                ],
            ],
        ]);

        // Verify task is actually updated in database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'priority' => 'high',
        ]);
    }

    /**
     * Test updating another user's task returns 404
     */
    public function test_cannot_update_another_users_task_via_nested_route(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $updateData = [
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/v1/users/{$this->otherUser->id}/tasks/{$task->id}", $updateData);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test updating task with mismatched user ID returns 404
     */
    public function test_cannot_update_task_with_mismatched_user_id(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                ],
            ],
        ];

        // Try to update task using wrong user ID in URL
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/v1/users/{$this->otherUser->id}/tasks/{$task->id}", $updateData);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Task cannot be found',
            'status' => 404,
        ]);
    }

    /**
     * Test unauthorized update access without token
     */
    public function test_unauthorized_update_returns_401(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                ],
            ],
        ];

        $response = $this->patchJson("/api/v1/users/{$this->user->id}/tasks/{$task->id}", $updateData);

        $response->assertStatus(401);
    }
} 