<?php

namespace App\Http\Requests\Kontrak;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahap'   => ['required', 'string', 'max:100'],
            'status'  => ['required', 'string', 'max:100'],
            'catatan' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan.required' => 'Teks catatan diperlukan.',
        ];
    }
}
