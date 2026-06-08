<?php

namespace App\Http\Requests\Kontrak;

use Illuminate\Foundation\Http\FormRequest;

class StoreKontrakRequest extends FormRequest
{
    private const NO_KONTRAK_PATTERN = '/^[A-Z0-9]+-\d{4}$/';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('no_kontrak')) {
            $this->merge([
                'no_kontrak' => strtoupper(trim((string) $this->input('no_kontrak'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'no_kontrak'                    => ['required', 'string', 'max:50', 'unique:kontrak,no_kontrak', 'regex:' . self::NO_KONTRAK_PATTERN],
            'status_kontrak'                => ['required', 'in:DRAF,DALAM_PELAKSANAAN,KONTRAK_SELESAI,EOT'],
            'tajuk_kontrak'                 => ['required', 'string'],
            'syarikat_id'                   => ['required', 'exists:syarikat,id'],
            'nilai_kontrak'                 => ['required', 'numeric', 'min:0'],
            'kaedah_perolehan'              => ['required', 'in:SEBUT HARGA,TENDER,RUNDINGAN TERUS,PEMBELIAN TERUS'],
            'kategori_perolehan'            => ['required', 'in:PERKHIDMATAN,BEKALAN,KERJA'],
            'pihak_berkuasa_melulus_nama'   => ['nullable', 'string', 'max:255'],
            'pihak_berkuasa_melulus_tarikh' => ['nullable', 'date'],
            'diluluskan_tarikh'             => ['nullable', 'date'],
            'ditandatangani_tarikh'         => ['nullable', 'date'],
            'mula_tarikh'                   => ['required', 'date'],
            'tamat_tarikh'                  => ['required', 'date', 'after_or_equal:mula_tarikh'],
            'tarikh_sst'                    => ['nullable', 'date'],
            'jabatan_id'                    => ['nullable', 'exists:jabatan,id'],
            'bahagian_unit_id'              => ['nullable', 'exists:bahagian_unit,id'],
            'pegawai_bertanggungjawab_id'   => ['required', 'exists:users,id'],
            'pegawai_perhubungan_1_id'      => ['nullable', 'exists:users,id'],
            'pegawai_perhubungan_2_id'      => ['nullable', 'exists:users,id'],
            'catatan_kontrak'               => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_kontrak.required'      => 'No kontrak wajib diisi.',
            'no_kontrak.unique'        => 'Nombor kontrak ini telah wujud.',
            'no_kontrak.regex'         => 'Format no. kontrak tidak sah. Contoh: BTMSH1-2025',
            'status_kontrak.required'  => 'Status kontrak wajib dipilih.',
            'tajuk_kontrak.required'   => 'Tajuk kontrak wajib diisi.',
            'syarikat_id.required'     => 'Nama syarikat wajib dipilih.',
            'tamat_tarikh.after_or_equal' => 'Tarikh tamat mesti sama atau selepas tarikh mula.',
            'nilai_kontrak.required'   => 'Nilai kontrak wajib diisi.',
            'kaedah_perolehan.required' => 'Kaedah perolehan wajib dipilih.',
            'kategori_perolehan.required' => 'Kategori perolehan wajib dipilih.',
            'mula_tarikh.required'     => 'Tarikh mula kontrak wajib diisi.',
            'tamat_tarikh.required'    => 'Tarikh tamat kontrak wajib diisi.',
            'pegawai_bertanggungjawab_id.required' => 'Pegawai bertanggungjawab wajib dipilih.',
            'syarikat_id.exists'       => 'Syarikat yang dipilih tidak dijumpai.',
        ];
    }
}
