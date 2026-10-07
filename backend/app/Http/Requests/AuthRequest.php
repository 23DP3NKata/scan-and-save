<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $email = ['required', 'string', 'email', 'max:30'];

        return match ($this->route()->getActionMethod()) {
            'register' => ['name' => ['required', 'string', 'regex:/^[\p{L}\p{N}]{3,30}$/u'], 'email' => [...$email, Rule::unique('users')], 'password' => ['required', 'string', 'min:5', 'max:255', 'confirmed']],
            'login' => ['email' => $email, 'password' => ['required', 'string', 'max:255'], 'remember' => ['sometimes', 'boolean']],
            default => [],
        };
    }

    public function messages(): array
    {
        return ['name.regex' => 'Use 3–30 letters and numbers for your username.'];
    }
}
