<?php

namespace App\Http\Requests\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'min:2', 'max:255'],
            'last_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults(), 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).+$/', 'min:8'],
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
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'email' => 'adresse email',
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
            'email.email' => __('auth.email.email'),
            'email.required' => __('auth.required'),
            'email.unique' => __('auth.email.unique'),
            'first_name.required' => __('auth.required'),
            'first_name.string' => __('auth.name.string'),
            'first_name.min' => __('auth.name.min'),
            'first_name.max' => __('auth.name.max'),
            'last_name.required' => __('auth.required'),
            'last_name.string' => __('auth.name.string'),
            'last_name.min' => __('auth.name.min'),
            'last_name.max' => __('auth.name.max'),
            'password.required' => __('auth.required'),
            'password.min' => __('auth.password.min'),
            'password.regex' => __('auth.password.regex'),
            'password.confirmed' => __('auth.password.confirmed'),
            'password_confirmation.required' => __('auth.required'),
            'password_confirmation.same' => __('auth.password.confirmed'),
        ];
    }
}
