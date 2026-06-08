<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatan = [
            ['kod' => 'BKP',   'nama' => 'Bahagian Kajian dan Pembangunan Perumahan'],
            ['kod' => 'BKT',   'nama' => 'Bahagian Kontrak'],
            ['kod' => 'BIS',   'nama' => 'Bahagian Integriti dan Standard'],
            ['kod' => 'BUK',   'nama' => 'Bahagian Undang-Undang dan Kawal Selia'],
            ['kod' => 'BTM',   'nama' => 'Bahagian Teknologi Maklumat'],
            ['kod' => 'JKT',   'nama' => 'Jabatan Kerja Teknikal'],
            ['kod' => 'JPET',  'nama' => 'Jabatan Penyelidikan dan Teknologi'],
            ['kod' => 'APM',   'nama' => 'Agensi Pengurusan Maklumat'],
            ['kod' => 'JUN',   'nama' => 'Jabatan Undang-Undang Negeri'],
            ['kod' => 'PIN',   'nama' => 'Pejabat Integriti Negeri'],
            ['kod' => 'PLANM', 'nama' => 'Perancang dan Landskap Nasional Malaysia'],
            ['kod' => 'TPPS',  'nama' => 'Timbalan Pengarah Perumahan Swasta'],
            ['kod' => 'URS',   'nama' => 'Unit Runding Syarikat'],
        ];

        foreach ($jabatan as $item) {
            Jabatan::updateOrCreate(['kod' => $item['kod']], $item);
        }

        $this->command->info('Jabatan seeded: ' . count($jabatan) . ' records.');
    }
}
