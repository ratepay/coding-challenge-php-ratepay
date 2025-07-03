<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BaseTaskRequest extends FormRequest
{
    public function mappedAttributes()
    {
        $attributeMap = [
            'data.attributes.title' => 'title',
            'data.attributes.description' => 'description',
            'data.attributes.status' => 'status',
            'data.attributes.priority' => 'priority',
            'data.attributes.due_date' => 'due_date',
            'data.relationships.user.data.id' => 'user_id',
        ];

        $attributesToUpdate = [];

        foreach ($attributeMap as $inputKey => $modelAttribute) {
            if ($this->has($inputKey)) {
                $attributesToUpdate[$modelAttribute] = $this->input($inputKey);
            }
        }

        return $attributesToUpdate;
    }
    
    public function messages()
    {
        return [
            'data.attributes.status.in' => 'The status value is invalid. Please use pending, in_progress, or completed.',
            'data.attributes.priority.in' => 'The priority value is invalid. Please use low, medium, or high.',
        ];
    }
} 