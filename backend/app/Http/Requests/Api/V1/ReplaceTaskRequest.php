<?php

namespace App\Http\Requests\Api\V1;

class ReplaceTaskRequest extends BaseTaskRequest
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
            'data.attributes.description' => 'required|string',
            'data.attributes.status' => 'required|string|in:pending,in_progress,completed',
            'data.attributes.priority' => 'required|string|in:low,medium,high',
            'data.attributes.due_date' => 'required|date',
            'data.relationships.user.data.id' => 'required|integer',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'data.attributes.status.in' => 'The status value is invalid. Please use pending, in_progress, or completed.',
            'data.attributes.priority.in' => 'The priority value is invalid. Please use low, medium, or high.',
        ];
    }
} 