<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ic_number'             => ['required', 'string', 'digits:12', 'unique:users,ic_number'],
            'name'                  => ['required_if:source,JBPM', 'nullable', 'string', 'max:255'],
            'email'                 => ['required_if:source,JBPM', 'nullable', 'email', 'unique:users,email'],
            'jabatan_bahagian'      => ['nullable', 'string', 'max:255'],
            'bahagian_unit'         => ['nullable', 'string', 'max:255'],
            'telefon_pejabat'       => ['nullable', 'string', 'max:20'],
            'telefon_bimbit'        => ['nullable', 'string', 'max:20'],
            'akses_scope'           => ['nullable', 'string', 'max:100'],
            'password'              => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'roles'                 => ['required', 'array', 'min:1'],
            'roles.*'               => ['string', 'exists:roles,name'],
            'source'                => ['required', 'in:BTM,JBPM,AGENSI'],
        ];
    }

    public function messages(): array
    {
        return [
            'ic_number.unique'   => 'Nombor IC ini telah didaftarkan.',
            'ic_number.digits'   => 'Nombor IC mestilah 12 digit tanpa sempang.',
            'email.unique'       => 'E-mel ini telah digunakan.',
            'roles.*.exists'     => 'Peranan yang dipilih tidak sah.',
        ];
    }
}
