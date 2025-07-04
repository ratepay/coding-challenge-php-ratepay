<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'type' => 'task',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->when(
                    $request->routeIs('tasks.show'),
                    $this->description
                ),
                'status' => $this->status,
                'priority' => $this->priority,
                'dueDate' => $this->due_date,
                $this->mergeWhen(
                    $request->routeIs('tasks.show'),
                    [
                        'createdAt' => $this->created_at,
                        'updatedAt' => $this->updated_at
                    ]
                ),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'user',
                        'id' => $this->user_id,
                    ],
                    'links' => [
                        'self' => route('users.show', ['user' => $this->user_id])
                    ]
                ],
            ],
            'includes' => $this->when(
                $request->routeIs('tasks.show'),
                [
                    new UserResource($this->whenLoaded('user'))
                ]
            ),
            'links' => [
                'self' => route('tasks.show', ['task' => $this->id])
            ]
        ];
    }
} 