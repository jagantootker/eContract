<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPasswordResetTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'token' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Emel berdaftar diperlukan.',
            'email.email' => 'Format emel tidak sah.',
            'token.required' => 'Token pengesahan diperlukan.',
            'token.digits' => 'Token pengesahan mesti 6 digit.',
        ];
    }
}