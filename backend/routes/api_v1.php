<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
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
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// // Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     // User routes
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
//     Route::put('/user', [AuthController::class, 'updateProfile']);
//     Route::post('/logout', [AuthController::class, 'logout']);
    
//     // Task routes
//     Route::apiResource('tasks', TaskController::class);
// }); 