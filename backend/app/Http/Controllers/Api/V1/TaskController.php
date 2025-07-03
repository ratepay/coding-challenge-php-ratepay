<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TaskFilter;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
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

        $model = [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'status' => $request->input('data.attributes.status'),
            'priority' => $request->input('data.attributes.priority'),
            'due_date' => $request->input('data.attributes.due_date'),
            'user_id' => $request->input('data.relationships.user.data.id'),
        ];

        return new TaskResource(Task::create($model));
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
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
} 