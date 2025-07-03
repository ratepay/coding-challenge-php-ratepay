<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Filters\V1\TaskFilter;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\ReplaceTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserTasksController extends ApiController
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

    /**
     * Store a newly created resource in storage.
     */
    public function store($userId, StoreTaskRequest $request)
    {
        // Check if user exists first
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        // Ensure the authenticated user can only create tasks for themselves
        if ($userId != auth()->id()) {
            return $this->error('Unauthorized', 403);
        }

        $model = [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'status' => $request->input('data.attributes.status'),
            'priority' => $request->input('data.attributes.priority'),
            'due_date' => $request->input('data.attributes.due_date'),
            'user_id' => $userId,
        ];

        return new TaskResource(Task::create($model));
    }

    /**
     * Replace the specified resource in storage.
     */
    public function replace(ReplaceTaskRequest $request, $userId, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Ensure the authenticated user can only replace their own tasks
            if ($task->user_id != $userId || $userId != auth()->id()) {
                return $this->error('Task cannot be found', 404);
            }

            $model = [
                'title' => $request->input('data.attributes.title'),
                'description' => $request->input('data.attributes.description'),
                'status' => $request->input('data.attributes.status'),
                'priority' => $request->input('data.attributes.priority'),
                'due_date' => $request->input('data.attributes.due_date'),
                'user_id' => $request->input('data.relationships.user.data.id'),
            ];
            
            $task->update($model);

            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            return $this->error('Task cannot be found', 404);
        }
    }

    public function show($user_id, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);

            // Verify that the task belongs to the specified user
            if ($task->user_id != $user_id) {
                return response()->json(['error' => 'Task cannot be found'], 404);
            }

            if ($this->include('user')) {
                return new TaskResource($task->load('user'));
            }
            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task cannot be found'], 404);
        }
    }

    public function destroy($user_id, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);

            // Verify that the task belongs to the specified user
            if ($task->user_id != $user_id) {
                return response()->json(['error' => 'Task cannot be found'], 404);
            }

            $task->delete();

            return response()->json(['message' => 'Task successfully deleted'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task cannot be found'], 404);
        }
    }
} 