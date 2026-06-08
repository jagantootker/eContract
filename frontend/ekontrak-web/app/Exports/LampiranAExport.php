<?php

namespace App\Exports;

use App\Services\ApiService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LampiranAExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ApiService $apiService,
        protected array $filters = []
    ) {}

    public function collection(): Collection
    {
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-a', array_merge($this->filters, ['per_page' => 9999]));
        $records = (array) ($result['data'] ?? []);

        return collect($records)->map(fn (array $row, int $index) => [
            $index + 1,
            $row['jabatan'] ?? '-',
            $row['bahagian_unit'] ?? '-',
            $row['tajuk_kontrak'] ?? '-',
            $row['kaedah_perolehan'] ?? '-',
            $row['tarikh_sst'] ?? '-',
            $row['tarikh_sst_disetujui_terima'] ?? '-',
            $row['tarikh_akhir_kontrak_perlu_dimatikan_setem'] ?? '-',
            $row['nama_syarikat'] ?? '-',
            $row['telah_tandatangan_tarikh_duti_setem'] ?? '-',
            $row['belum_tandatangan_status_tarikh_pergerakan'] ?? '-',
            $row['sebab_lewat_tandatangan'] ?? '-',
            $row['catatan_kontrak'] ?? '-',
        ]);
    }

    public function headings(): array
    {
        return [
            '#',
            'JABATAN / BAHAGIAN',
            'BAHAGIAN / UNIT',
            'TAJUK PEROLEHAN',
            'KAEDAH PEROLEHAN',
            'TARIKH SST',
            'TARIKH SST DISETUJU TERIMA',
            'TARIKH AKHIR KONTRAK PERLU DIMATIKAN SETEM',
            'NAMA PEMBEKAL',
            'TELAH TANDATANGAN (TARIKH DUTI SETEM)',
            'BELUM TANDATANGAN (STATUS & TARIKH PERGERAKAN KONTRAK)',
            'SILA NYATAKAN SEBAB JIKA KONTRAK DITANDATANGANI LEBIH 3 BULAN DARI TARIKH SST DISETUJU TERIMA',
            'CATATAN',
        ];
    }
}
