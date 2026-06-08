<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatan = [
            ['kod' => 'BKP',   'nama' => 'Bahagian Kajian dan Pembangunan Perumahan', 'is_visible_in_registration' => false],
            ['kod' => 'BKT',   'nama' => 'Bahagian Kontrak', 'is_visible_in_registration' => false],
            ['kod' => 'BIS',   'nama' => 'Bahagian Integriti dan Standard', 'is_visible_in_registration' => false],
            ['kod' => 'BUK',   'nama' => 'Bahagian Undang-Undang dan Kawal Selia', 'is_visible_in_registration' => false],
            ['kod' => 'BTM',   'nama' => 'Bahagian Teknologi Maklumat', 'is_visible_in_registration' => false],
            ['kod' => 'BOMBA', 'nama' => 'Jabatan Bomba dan Penyelamat Malaysia', 'is_visible_in_registration' => true],
            ['kod' => 'LANDSKAP', 'nama' => 'Jabatan Landskap Negara', 'is_visible_in_registration' => true],
            ['kod' => 'JKT',   'nama' => 'Jabatan Kerja Teknikal', 'is_visible_in_registration' => false],
            ['kod' => 'JPET',  'nama' => 'Jabatan Penyelidikan dan Teknologi', 'is_visible_in_registration' => false],
            ['kod' => 'APM',   'nama' => 'Agensi Pengurusan Maklumat', 'is_visible_in_registration' => false],
            ['kod' => 'JUN',   'nama' => 'Jabatan Undang-Undang Negeri', 'is_visible_in_registration' => false],
            ['kod' => 'PIN',   'nama' => 'Pejabat Integriti Negeri', 'is_visible_in_registration' => false],
            ['kod' => 'PLANM', 'nama' => 'Perancang dan Landskap Nasional Malaysia', 'is_visible_in_registration' => false],
            ['kod' => 'PLANMALAYSIA', 'nama' => 'PLANMalaysia', 'is_visible_in_registration' => true],
            ['kod' => 'TPPS',  'nama' => 'Timbalan Pengarah Perumahan Swasta', 'is_visible_in_registration' => false],
            ['kod' => 'URS',   'nama' => 'Unit Runding Syarikat', 'is_visible_in_registration' => false],
        ];

        foreach ($jabatan as $item) {
            Jabatan::updateOrCreate(['kod' => $item['kod']], $item);
        }

        $this->command->info('Jabatan seeded: ' . count($jabatan) . ' records.');
    }
}
