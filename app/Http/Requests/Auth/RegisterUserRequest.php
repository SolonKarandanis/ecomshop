<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:200'],
            'email' => ['required', 'email', 'max:200', 'unique:users'],
            'password' => ['required', 'string', Rules\Password::defaults()]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
