<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class invitationRequest extends FormRequest
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
            'password' => ['required', 'confirmed', Password::defaults(), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).+$/', 'min:8'],
            'token' => ['required', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'password.required' => __('auth.required'),
            'password.min' => __('auth.password.min'),
            'password.regex' => __('auth.password.regex'),
            'password.confirmed' => __('auth.password.confirmed'),
            'password_confirmation.required' => __('auth.required'),
            'password_confirmation.same' => __('auth.password.confirmed'),
            'token.required' => __('auth.required'),
            'token.string' => __('auth.string'),
        ];
    }
}
