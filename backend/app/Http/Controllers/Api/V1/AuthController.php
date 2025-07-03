<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Register a new user
     * 
     * TODO: Implement user registration
     * Expected fields: name, email, password, password_confirmation
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // TODO: Implement user registration
        // 1. Validate input data
        // 2. Create new user
        // 3. Generate authentication token
        // 4. Return success response with token
        
        return $this->success('User registered successfully', [
            'user' => [],
            'token' => ''
        ], 201);
    }

    /**
     * Login user
     * 
     * TODO: Implement user login
     * Expected fields: email, password
     * 
     * @param LoginUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginUserRequest $request)
    {
        // TODO: Implement user login
        // 1. Validate input data ✓ (handled by LoginUserRequest)
        // 2. Attempt authentication
        // 3. Generate authentication token
        // 4. Return success response with token
        
        $credentials = $request->validated();
        
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok('Authenticated', [
            'token' => $user->createToken(
                'Api token for ' . $user->email,
                ['*'],
                now()->addMonth()
            )->plainTextToken,
        ]);
    }

    /**
     * Logout user
     * 
     * TODO: Implement user logout
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // TODO: Implement user logout
        // 1. Revoke current access token ✓
        // 2. Return success response ✓
        
        $request->user()->currentAccessToken()->delete();
        return $this->ok('Logout successful');
    }

    /**
     * Update user profile
     * 
     * TODO: Implement profile update
     * Expected fields: name, email (optional)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        // TODO: Implement profile update
        // 1. Validate input data
        // 2. Update user data
        // 3. Return success response with updated user
        
        return $this->success('Profile updated successfully', []);
    }
} 