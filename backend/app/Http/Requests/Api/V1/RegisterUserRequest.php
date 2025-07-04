<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.email' => 'required|string|email|max:255|unique:users,email',
            'data.attributes.password' => ['required', 'string', 'confirmed', Password::defaults()],
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
            'data.attributes.name.max' => 'The name may not be greater than 255 characters.',
            'data.attributes.email.required' => 'The email field is required.',
            'data.attributes.email.email' => 'The email must be a valid email address.',
            'data.attributes.email.max' => 'The email may not be greater than 255 characters.',
            'data.attributes.email.unique' => 'The email has already been taken.',
            'data.attributes.password.required' => 'The password field is required.',
            'data.attributes.password.confirmed' => 'The password confirmation does not match.',
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

        return [
            'name' => $validated['data']['attributes']['name'],
            'email' => $validated['data']['attributes']['email'],
            'password' => $validated['data']['attributes']['password'],
        ];
    }
} 