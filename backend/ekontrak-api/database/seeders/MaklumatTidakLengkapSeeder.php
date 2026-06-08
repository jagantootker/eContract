<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Kontrak;
use App\Models\Syarikat;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaklumatTidakLengkapSeeder extends Seeder
{
    public function run(): void
    {
        $syarikatId = Syarikat::query()->value('id');
        $userId = User::query()->value('id');
        $jabatanId = Jabatan::query()->where('kod', 'BTM')->value('id')
            ?? Jabatan::query()->value('id');

        if (! $syarikatId || ! $userId || ! $jabatanId) {
            return;
        }

        $rows = [
            [
                'no_kontrak' => 'INCOMPLETE1-2026',
                'tajuk_kontrak' => 'Kontrak Ujian Maklumat Tidak Lengkap 1',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 125000.00,
                'status_kontrak' => 'DRAF',
                'mula_tarikh' => null,
                'tamat_tarikh' => now()->addMonths(4)->toDateString(),
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ],
            [
                'no_kontrak' => 'INCOMPLETE2-2026',
                'tajuk_kontrak' => 'Kontrak Ujian Maklumat Tidak Lengkap 2',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 215000.00,
                'status_kontrak' => 'DALAM_PELAKSANAAN',
                'mula_tarikh' => now()->subDays(10)->toDateString(),
                'tamat_tarikh' => null,
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ],
            [
                'no_kontrak' => 'INCOMPLETE3-2026',
                'tajuk_kontrak' => 'Kontrak Ujian Maklumat Tidak Lengkap 3',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 178000.00,
                'status_kontrak' => 'EOT',
                'mula_tarikh' => null,
                'tamat_tarikh' => null,
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ],
        ];

        foreach ($rows as $row) {
            Kontrak::updateOrCreate(
                ['no_kontrak' => $row['no_kontrak']],
                $row
            );
        }
    }
}
