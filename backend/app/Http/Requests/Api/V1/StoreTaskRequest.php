<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
        return [
            'data.attributes.title' => 'required|string|max:255',
            'data.attributes.description' => 'nullable|string',
            'data.attributes.status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'data.attributes.priority' => 'required|string|in:low,medium,high',
            'data.attributes.due_date' => 'nullable|date|after:today',
        ];
    }

    public function messages()
    {
        return [
            'data.attributes.status.in' => 'The status value is invalid. Please use pending, in_progress, completed, or cancelled.',
            'data.attributes.priority.in' => 'The priority value is invalid. Please use low, medium, or high.',
            'data.attributes.due_date.after' => 'The due date must be a future date.',
        ];
    }
} 