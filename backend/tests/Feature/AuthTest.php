<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * Test successful user login
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert successful response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token'
                ],
                'status'
            ])
            ->assertJson([
                'message' => 'Authenticated',
                'status' => 200
            ]);

        // Assert token is present and valid
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test login with invalid email
     */
    public function test_user_cannot_login_with_invalid_email(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt to login with wrong email
        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // Assert error response
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
                'status' => 401
            ]);
    }

    /**
     * Test login with invalid password
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt to login with wrong password
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert error response
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
                'status' => 401
            ]);
    }

    /**
     * Test login validation - missing email
     */
    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login validation - missing password
     */
    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login validation - invalid email format
     */
    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login validation - password minimum length
     */
    public function test_login_requires_password_minimum_length(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => '123', // Less than 8 characters
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test successful user logout
     */
    public function test_user_can_logout_successfully(): void
    {
        // Create a user and generate a token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Make authenticated request with token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        // Assert successful response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
                'status' => 200
            ]);

        // Assert the user's tokens are deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);
    }

    /**
     * Test logout requires authentication
     */
    public function test_logout_requires_authentication(): void
    {
        // Attempt to logout without authentication
        $response = $this->postJson('/api/v1/logout');

        // Assert unauthorized response
        $response->assertStatus(401);
    }

    /**
     * Test logout revokes current token
     */
    public function test_logout_revokes_current_token(): void
    {
        // Create a user and generate a token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Make authenticated request with token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        // Assert successful logout
        $response->assertStatus(200);

        // Assert the user's tokens are deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);
    }

    /**
     * Test multiple login attempts generate different tokens
     */
    public function test_multiple_logins_generate_different_tokens(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // First login
        $response1 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Second login
        $response2 = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert both logins are successful
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Assert tokens are different
        $token1 = $response1->json('data.token');
        $token2 = $response2->json('data.token');

        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test login with empty request body
     */
    public function test_login_with_empty_request_body(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login with non-existent user
     */
    public function test_login_with_nonexistent_user(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
                'status' => 401
            ]);
    }

    /**
     * Test user registration with valid data
     */
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john_register@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at'
                    ],
                    'token'
                ],
                'status'
            ])
            ->assertJson([
                'message' => 'User registered successfully',
                'status' => 201,
                'data' => [
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john_register@example.com'
                    ]
                ]
            ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john_register@example.com'
        ]);

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Api token for john_register@example.com'
        ]);
    }

    /**
     * Test user registration with invalid email
     */
    public function test_user_cannot_register_with_invalid_email()
    {
        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'invalid-email',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    /**
     * Test user registration with duplicate email
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        // Create a user first
        User::factory()->create(['email' => 'john_duplicate@example.com']);

        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john_duplicate@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    /**
     * Test user registration with mismatched passwords
     */
    public function test_user_cannot_register_with_mismatched_passwords()
    {
        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'differentpassword',
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.password']);
    }

    /**
     * Test user registration with missing required fields
     */
    public function test_user_cannot_register_with_missing_fields()
    {
        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    // Missing email and password
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email', 'data.attributes.password']);
    }

    /**
     * Test user registration with weak password
     */
    public function test_user_cannot_register_with_weak_password()
    {
        $userData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => '123', // Too short
                    'password_confirmation' => '123',
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.password']);
    }

    /**
     * Test user profile update with valid data
     */
    public function test_user_can_update_profile_with_valid_data()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john_update@example.com',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane_update@example.com',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'updated_at'
                    ]
                ],
                'status'
            ])
            ->assertJson([
                'message' => 'Profile updated successfully',
                'status' => 200,
                'data' => [
                    'user' => [
                        'name' => 'Jane Doe',
                        'email' => 'jane_update@example.com'
                    ]
                ]
            ]);

        // Verify user was updated in database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane_update@example.com'
        ]);
    }

    /**
     * Test user profile update with only name
     */
    public function test_user_can_update_profile_with_only_name()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john_name@example.com',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'Jane Doe',
                        'email' => 'john_name@example.com' // Should remain unchanged
                    ]
                ]
            ]);

        // Verify only name was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'john_name@example.com'
        ]);
    }

    /**
     * Test user profile update with only email
     */
    public function test_user_can_update_profile_with_only_email()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john_email@example.com',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'email' => 'jane_email@example.com',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'John Doe', // Should remain unchanged
                        'email' => 'jane_email@example.com'
                    ]
                ]
            ]);

        // Verify only email was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'jane_email@example.com'
        ]);
    }

    /**
     * Test profile update requires authentication
     */
    public function test_profile_update_requires_authentication()
    {
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                ]
            ]
        ];

        $response = $this->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(401);
    }

    /**
     * Test profile update with invalid email
     */
    public function test_user_cannot_update_profile_with_invalid_email()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'email' => 'invalid-email',
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    /**
     * Test profile update with duplicate email
     */
    public function test_user_cannot_update_profile_with_duplicate_email()
    {
        $user1 = User::factory()->create(['email' => 'john_duplicate1@example.com']);
        $user2 = User::factory()->create(['email' => 'jane_duplicate1@example.com']);

        $token = $user1->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'email' => 'jane_duplicate1@example.com', // Already taken by user2
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    /**
     * Test profile update with too short name
     */
    public function test_user_cannot_update_profile_with_too_short_name()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'A', // Too short (min: 2)
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.name']);
    }

    /**
     * Test profile update with too short email
     */
    public function test_user_cannot_update_profile_with_too_short_email()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'email' => 'a@b', // Too short (min: 5)
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    /**
     * Test user can update to their own email (no conflict)
     */
    public function test_user_can_update_to_their_own_email()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john_own@example.com',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'John Smith',
                    'email' => 'john_own@example.com', // Same email as current user
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'John Smith',
                        'email' => 'john_own@example.com'
                    ]
                ]
            ]);
    }
} 