<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Kontrak;
use App\Models\Syarikat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DashboardAlertSeeder extends Seeder
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

        $today = Carbon::today();

        Kontrak::updateOrCreate(
            ['no_kontrak' => 'BTMALERT1-2026'],
            [
                'tajuk_kontrak' => 'Kontrak Ujian Dashboard - Tamat Tempoh',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 150000.00,
                'kaedah_perolehan' => 'SEBUT HARGA',
                'kategori_perolehan' => 'PERKHIDMATAN',
                'mula_tarikh' => $today->copy()->subMonths(3),
                'tamat_tarikh' => $today->copy()->subDays(2),
                'status_kontrak' => 'DALAM_PELAKSANAAN',
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ]
        );

        Kontrak::updateOrCreate(
            ['no_kontrak' => 'BTMALERT2-2026'],
            [
                'tajuk_kontrak' => 'Kontrak Ujian Dashboard - Akan Tamat',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 175000.00,
                'kaedah_perolehan' => 'TENDER',
                'kategori_perolehan' => 'BEKALAN',
                'mula_tarikh' => $today->copy()->subMonths(2),
                'tamat_tarikh' => $today->copy()->addDays(7),
                'status_kontrak' => 'DALAM_PELAKSANAAN',
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ]
        );

        Kontrak::updateOrCreate(
            ['no_kontrak' => 'BTMALERT3-2026'],
            [
                'tajuk_kontrak' => 'Kontrak Ujian Dashboard - Draf',
                'syarikat_id' => $syarikatId,
                'nilai_kontrak' => 99000.00,
                'kaedah_perolehan' => 'PEMBELIAN TERUS',
                'kategori_perolehan' => 'KERJA',
                'mula_tarikh' => $today->copy()->addDays(14),
                'tamat_tarikh' => $today->copy()->addMonths(4),
                'status_kontrak' => 'DRAF',
                'jabatan_id' => $jabatanId,
                'created_by' => $userId,
            ]
        );
    }
}
