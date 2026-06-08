<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'  => 'Kata laluan semasa diperlukan.',
            'new_password.required'      => 'Kata laluan baharu diperlukan.',
            'new_password.confirmed'     => 'Pengesahan kata laluan baharu tidak sepadan.',
            'new_password.min'           => 'Kata laluan mestilah sekurang-kurangnya 12 aksara.',
        ];
    }
}
