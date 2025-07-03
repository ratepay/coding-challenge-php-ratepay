<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends BaseTaskRequest
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
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'nullable|string',
            'data.attributes.status' => 'nullable|string|in:pending,in_progress,completed',
            'data.attributes.priority' => 'nullable|string|in:low,medium,high',
            'data.attributes.due_date' => 'nullable|date',
        ];

        if ($this->routeIs('tasks.store')) {
            $rules['data.relationships.user.data.id'] = 'required|integer';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'data.attributes.status.in' => 'The status value is invalid. Please use pending, in_progress, or completed.',
            'data.attributes.priority.in' => 'The priority value is invalid. Please use low, medium, or high.',
            'data.attributes.due_date.after' => 'The due date must be a future date.',
        ];
    }

    public function mappedAttributes()
    {
        $attributes = parent::mappedAttributes();
        
        // For nested routes, set user_id from the route parameter
        if ($this->route('user')) {
            $attributes['user_id'] = $this->route('user');
        }
        
        return $attributes;
    }
} 