<?php

namespace Database\Seeders;

use App\Models\Kontrak;
use App\Models\Jabatan;
use App\Models\Syarikat;
use App\Models\User;
use Illuminate\Database\Seeder;

class KontrakSeeder extends Seeder
{
    public function run(): void
    {
        $syarikatIds = Syarikat::pluck('id');
        $userIds = User::pluck('id');
        $jabatanMap = Jabatan::pluck('id', 'kod');
        $defaultJabatanId = $jabatanMap->get('BTM');
        $kaedah = ['SEBUT HARGA', 'TENDER', 'RUNDINGAN TERUS', 'PEMBELIAN TERUS'];
        $kategori = ['PERKHIDMATAN', 'BEKALAN', 'KERJA'];
        $status = ['DRAF', 'DALAM_PELAKSANAAN', 'KONTRAK_SELESAI', 'EOT'];
        for ($i = 1; $i <= 30; $i++) {
            Kontrak::updateOrCreate([
                'no_kontrak' => 'BTM/MG/2026/' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ], [
                'tajuk_kontrak' => 'Kontrak Projek ' . $i,
                'syarikat_id' => $syarikatIds->random(),
                'nilai_kontrak' => rand(100000, 1000000),
                'kaedah_perolehan' => $kaedah[array_rand($kaedah)],
                'kategori_perolehan' => $kategori[array_rand($kategori)],
                'pihak_berkuasa_melulus_nama' => 'Pihak Berkuasa ' . $i,
                'pihak_berkuasa_melulus_tarikh' => now()->subDays(rand(1, 1000)),
                'diluluskan_tarikh' => now()->subDays(rand(1, 900)),
                'ditandatangani_tarikh' => now()->subDays(rand(1, 800)),
                'mula_tarikh' => now()->subDays(rand(1, 700)),
                'tamat_tarikh' => now()->addDays(rand(1, 700)),
                'tarikh_sst' => now()->subDays(rand(1, 600)),
                'status_kontrak' => $status[array_rand($status)],
                'status_draf_kompan' => (bool)rand(0, 1),
                'tarikh_draf_hantar_sistem' => now()->subDays(rand(1, 500)),
                'catatan_kontrak' => 'Catatan untuk kontrak ' . $i,
                'jabatan_id' => $defaultJabatanId,
                'bahagian_unit_id' => null,
                'pegawai_bertanggungjawab_id' => $userIds->random(),
                'pegawai_perhubungan_1_id' => $userIds->random(),
                'pegawai_perhubungan_2_id' => $userIds->random(),
                'created_by' => $userIds->random(),
            ]);
        }
    }
}
