<?php

namespace App\Exports;

use App\Services\ApiService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LampiranBExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ApiService $apiService,
        protected array $filters = []
    ) {}

    public function collection(): Collection
    {
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-b', array_merge($this->filters, ['per_page' => 9999]));
        $records = (array) ($result['data'] ?? []);

        return collect($records)->map(function (array $row, int $index) {
            $tempoh = (int) ($row['tempoh_bulan'] ?? 0);
            return [
                $index + 1,
                $row['jabatan'] ?? '-',
                $row['bahagian_unit'] ?? '-',
                $row['tajuk_kontrak'] ?? '-',
                $row['kaedah_perolehan'] ?? '-',
                $row['tarikh_sst_disetujui_terima'] ?? '-',
                $row['mula_tarikh'] ?? '-',
                $row['tamat_tarikh'] ?? '-',
                $tempoh > 0 ? $tempoh . ' Bulan' : '-',
                $row['nama_syarikat'] ?? '-',
                $row['catatan_kontrak'] ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'JABATAN / BAHAGIAN',
            'BAHAGIAN / UNIT',
            'TAJUK PEROLEHAN',
            'KAEDAH PEROLEHAN',
            'TARIKH SST DISETUJU TERIMA',
            'TARIKH MULA PERKHIDMATAN',
            'TARIKH TAMAT PERKHIDMATAN',
            'TEMPOH KONTRAK',
            'NAMA PEMBEKAL',
            'CATATAN',
        ];
    }
}
