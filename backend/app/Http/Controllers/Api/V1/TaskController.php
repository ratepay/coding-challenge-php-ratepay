<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TaskFilter;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Requests\Api\V1\ReplaceTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(TaskFilter $filters)
    {
        return TaskResource::collection(
            Task::where('user_id', auth()->id())
                ->filter($filters)
                ->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        try {
            $user = User::findOrFail($request->input('data.relationships.user.data.id'));
        } catch (ModelNotFoundException $e) {
            return $this->error('User not found', [
                'error' => 'The provided user ID does not exist.'
            ], 404);
        }

        return new TaskResource(Task::create($request->mappedAttributes()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if ($this->include('user')) {
            return new TaskResource($task->load('user'));
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Ensure the authenticated user can only update their own tasks
            if ($task->user_id != auth()->id()) {
                return $this->error('Task cannot be found', 404);
            }

            $task->update($request->mappedAttributes());

            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            return $this->error('Task cannot be found', 404);
        }
    }

    /**
     * Replace the specified resource in storage.
     */
    public function replace(ReplaceTaskRequest $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Ensure the authenticated user can only replace their own tasks
            if ($task->user_id != auth()->id()) {
                return $this->error('Task cannot be found', 404);
            }

            $task->update($request->mappedAttributes());

            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            return $this->error('Task cannot be found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Ensure the authenticated user can only delete their own tasks
            if ($task->user_id != auth()->id()) {
                return $this->error('Task cannot be found', 404);
            }
            
            $task->delete();

            return response()->json(['message' => 'Task successfully deleted'], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('Task cannot be found', 404);
        }
    }
} 