<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TaskFilter;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserTasksController extends Controller
{
    public function index($user_id, TaskFilter $filters) 
    {
        // Check if user exists
        try {
            $user = User::findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        return TaskResource::collection(
            Task::where('user_id', $user_id)
                ->filter($filters)
                ->paginate()
        );
    }

    public function store($user_id, StoreTaskRequest $request) 
    {
        // Check if user exists
        try {
            $user = User::findOrFail($user_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $model = [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'status' => $request->input('data.attributes.status'),
            'priority' => $request->input('data.attributes.priority'),
            'due_date' => $request->input('data.attributes.due_date'),
            'user_id' => $user_id,
        ];

        return new TaskResource(Task::create($model));
    }
} 