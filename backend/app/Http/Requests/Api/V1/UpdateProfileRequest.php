<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'data.attributes.name' => 'sometimes|required|string|min:2|max:255',
            'data.attributes.email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'min:5',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'data.attributes.name.required' => 'The name field is required.',
            'data.attributes.name.min' => 'The name must be at least 2 characters.',
            'data.attributes.name.max' => 'The name may not be greater than 255 characters.',
            'data.attributes.email.required' => 'The email field is required.',
            'data.attributes.email.email' => 'The email must be a valid email address.',
            'data.attributes.email.min' => 'The email must be at least 5 characters.',
            'data.attributes.email.max' => 'The email may not be greater than 255 characters.',
            'data.attributes.email.unique' => 'The email has already been taken.',
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        $data = [];
        
        if (isset($validated['data']['attributes']['name'])) {
            $data['name'] = $validated['data']['attributes']['name'];
        }
        
        if (isset($validated['data']['attributes']['email'])) {
            $data['email'] = $validated['data']['attributes']['email'];
        }

        return $data;
    }
} 