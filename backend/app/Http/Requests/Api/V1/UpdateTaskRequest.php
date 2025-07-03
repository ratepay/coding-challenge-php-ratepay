<?php

namespace App\Http\Requests\Api\V1;

class UpdateTaskRequest extends BaseTaskRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'data.attributes.title' => 'sometimes|required|string',
            'data.attributes.description' => 'sometimes|required|string',
            'data.attributes.status' => 'sometimes|required|string|in:pending,in_progress,completed',
            'data.attributes.priority' => 'sometimes|required|string|in:low,medium,high',
            'data.attributes.due_date' => 'sometimes|required|date',
            'data.relationships.user.data.id' => 'sometimes|integer',
        ];

        return $rules;
    }
} 