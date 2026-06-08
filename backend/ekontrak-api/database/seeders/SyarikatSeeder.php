<?php

namespace Database\Seeders;

use App\Models\Syarikat;
use App\Models\User;
use Illuminate\Database\Seeder;

class SyarikatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->take(10)->pluck('id');
        $negeri = ['Selangor', 'Kuala Lumpur', 'Johor', 'Penang', 'Perak', 'Sabah', 'Sarawak', 'Pahang', 'Kelantan', 'Terengganu'];
        for ($i = 1; $i <= 20; $i++) {
            Syarikat::updateOrCreate([
                'nama_syarikat' => 'Syarikat Contoh ' . $i,
            ], [
                'alamat' => 'Alamat Syarikat ' . $i . ', Jalan Contoh, Bandar Contoh',
                'negeri' => $negeri[array_rand($negeri)],
                'pegawai_hubungi_1_nama' => 'Pegawai 1 Syarikat ' . $i,
                'pegawai_hubungi_1_email' => 'pegawai1_' . $i . '@syarikat.com',
                'pegawai_hubungi_1_tel_pejabat' => '03-8888' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pegawai_hubungi_1_tel_hp' => '012-3456' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pegawai_hubungi_2_nama' => 'Pegawai 2 Syarikat ' . $i,
                'pegawai_hubungi_2_email' => 'pegawai2_' . $i . '@syarikat.com',
                'pegawai_hubungi_2_tel_pejabat' => '03-9999' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pegawai_hubungi_2_tel_hp' => '013-4567' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pegawai_hubungi_3_nama' => 'Pegawai 3 Syarikat ' . $i,
                'pegawai_hubungi_3_email' => 'pegawai3_' . $i . '@syarikat.com',
                'pegawai_hubungi_3_tel_pejabat' => '03-7777' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pegawai_hubungi_3_tel_hp' => '014-5678' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'created_by' => $users->random(),
            ]);
        }
    }
}
