<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_permohonan'             => ['required', 'in:pendaftaran_online,pengaktifan_semula_id,penukaran_peranan'],
            'ic_number'                    => ['required', 'string', 'max:20', 'unique:users,ic_number'],
            'no_tentera'                   => ['nullable', 'string', 'max:20'],
            'name'                         => ['required', 'string', 'max:255'],
            'email'                        => ['required', 'email', 'max:255', 'unique:users,email'],
            'jabatan_bahagian'             => ['nullable', 'string', 'max:255'],
            'bahagian_unit'                => ['nullable', 'string', 'max:255'],
            'telefon_pejabat'              => ['nullable', 'string', 'max:20'],
            'telefon_bimbit'               => ['nullable', 'string', 'max:20'],
            'kategori_permohonan'          => ['required', 'in:agensi,pengguna'],
            'source'                       => ['nullable', 'in:BTM,JBPM,AGENSI'],
            'peranan'                      => ['required', 'array', 'min:1'],
            'peranan.*'                    => ['string', 'exists:roles,name'],
            'akses_scope'                  => ['nullable', 'string', 'max:100'],
            'lampiran_borang_permohonan'   => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'lampiran_kp_tentera'          => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'lampiran_pas_pekerja'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'password'                     => [
                'required',
                'string',
                'confirmed',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ic_number.unique' => 'Nombor kad pengenalan/no. tentera ini telah didaftarkan.',
            'kategori_permohonan.required' => 'Kategori permohonan perlu dipilih.',
            'peranan.required' => 'Sila pilih sekurang-kurangnya satu peranan.',
            'peranan.*.exists' => 'Peranan yang dipilih tidak sah.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 12 aksara.',
            'password.regex' => 'Kata laluan mesti mengandungi huruf besar, huruf kecil, nombor dan aksara khas.',
            'lampiran_borang_permohonan.required' => 'Borang permohonan wajib dimuat naik.',
            'lampiran_kp_tentera.required' => 'Salinan kad pengenalan/tentera wajib dimuat naik.',
            'lampiran_pas_pekerja.required' => 'Pas pekerja wajib dimuat naik.',
        ];
    }
}
