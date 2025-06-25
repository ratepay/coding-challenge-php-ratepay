<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
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
        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [],
                'token' => ''
            ]
        ], 201);
    }

    /**
     * Login user
     * 
     * TODO: Implement user login
     * Expected fields: email, password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // TODO: Implement user login
        // 1. Validate input data
        // 2. Attempt authentication
        // 3. Generate authentication token
        // 4. Return success response with token
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [],
                'token' => ''
            ]
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
        // 1. Revoke current access token
        // 2. Return success response
        
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
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
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => []
        ]);
    }
}
