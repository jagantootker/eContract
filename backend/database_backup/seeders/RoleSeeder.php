<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',                  'label' => 'Admin'],
            ['name' => 'pendaftar_kontrak',       'label' => 'Pendaftar Kontrak'],
            ['name' => 'pemilik_projek',          'label' => 'Pemilik Projek'],
            ['name' => 'admin_sistem',            'label' => 'Admin Sistem'],
            ['name' => 'pegawai_undang_undang',   'label' => 'Pegawai Undang-Undang'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $this->command->info('Roles seeded: ' . count($roles) . ' roles.');
    }
}
