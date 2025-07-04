<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Controllers\Api\V1\UserTasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for version 1 of your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Task routes
    Route::apiResource('tasks', TaskController::class)->except('update');
    Route::put('tasks/{task}', [TaskController::class, 'replace']);
    Route::patch('tasks/{task}', [TaskController::class, 'update']);
    
    // Users routes
    Route::apiResource('users', UsersController::class);
    
    // Nested user tasks routes
    Route::apiResource('users.tasks', UserTasksController::class)->except('update');
    Route::put('users/{user}/tasks/{task}', [UserTasksController::class, 'replace']);
    Route::patch('users/{user}/tasks/{task}', [UserTasksController::class, 'update']);
}); 