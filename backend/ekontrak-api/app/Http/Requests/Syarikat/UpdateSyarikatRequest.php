<?php

namespace App\Http\Requests\Syarikat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSyarikatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_syarikat'                  => ['sometimes', 'string', 'max:255'],
            'alamat'                         => ['sometimes', 'string'],
            'negeri'                         => ['sometimes', 'string', 'max:100'],
            'pegawai_hubungi_1_nama'         => ['required', 'string', 'max:255'],
            'pegawai_hubungi_1_email'        => ['required', 'email', 'max:255'],
            'pegawai_hubungi_1_tel_pejabat'  => ['required', 'string', 'max:20'],
            'pegawai_hubungi_1_tel_hp'       => ['required', 'string', 'max:20'],
            'pegawai_hubungi_2_nama'         => ['nullable', 'string', 'max:255'],
            'pegawai_hubungi_2_email'        => ['nullable', 'email', 'max:255'],
            'pegawai_hubungi_2_tel_pejabat'  => ['nullable', 'string', 'max:20'],
            'pegawai_hubungi_2_tel_hp'       => ['nullable', 'string', 'max:20'],
            'pegawai_hubungi_3_nama'         => ['nullable', 'string', 'max:255'],
            'pegawai_hubungi_3_email'        => ['nullable', 'email', 'max:255'],
            'pegawai_hubungi_3_tel_pejabat'  => ['nullable', 'string', 'max:20'],
            'pegawai_hubungi_3_tel_hp'       => ['nullable', 'string', 'max:20'],
        ];
    }
}
