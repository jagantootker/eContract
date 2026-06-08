<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'             => ['nullable', 'string', 'max:255'],
            'email'            => ['nullable', 'email', "unique:users,email,{$userId}"],
            'jabatan_bahagian' => ['nullable', 'string', 'max:255'],
            'bahagian_unit'    => ['nullable', 'string', 'max:255'],
            'telefon_pejabat'  => ['nullable', 'string', 'max:20'],
            'telefon_bimbit'   => ['nullable', 'string', 'max:20'],
            'akses_scope'      => ['nullable', 'string', 'max:100'],
            'password'         => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'roles'            => ['nullable', 'array'],
            'roles.*'          => ['string', 'exists:roles,name'],
            'source'           => ['nullable', 'in:BTM,JBPM,AGENSI'],
        ];
    }
}
