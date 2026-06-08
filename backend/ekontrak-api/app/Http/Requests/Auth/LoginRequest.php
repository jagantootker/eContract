<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ic_number' => ['required', 'string', 'digits:12'],
            'password'  => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ic_number.required' => 'Nombor IC diperlukan.',
            'ic_number.digits'   => 'Nombor IC mestilah 12 digit tanpa sempang.',
            'password.required'  => 'Kata laluan diperlukan.',
        ];
    }
}
