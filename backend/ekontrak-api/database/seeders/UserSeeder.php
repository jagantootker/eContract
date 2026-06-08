<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'pendaftar_kontrak',
            'pemilik_projek',
            'admin_sistem',
            'pegawai_undang_undang',
        ];

        $sources = ['BTM', 'JBPM', 'AGENSI'];

        // Create 10 users for each role
        foreach ($roles as $roleIndex => $roleName) {
            $role = Role::where('name', $roleName)->first();
            for ($i = 1; $i <= 10; $i++) {
                $ic = sprintf('%02d%010d', $roleIndex + 1, $i);
                $user = User::updateOrCreate(
                    [
                        'ic_number' => $ic,
                    ],
                    [
                        'name' => ucwords(str_replace('_', ' ', $roleName)) . ' User ' . $i,
                        'email' => $roleName . $i . '@ekontrak.gov.my',
                        'password' => Hash::make('Password@' . $i),
                        'jabatan_bahagian' => 'BTM',
                        'telefon_pejabat' => '03-8888' . str_pad($i, 4, '0', STR_PAD_LEFT),
                        'telefon_bimbit' => '012-3456' . str_pad($i, 4, '0', STR_PAD_LEFT),
                        'is_active' => true,
                        'source' => $sources[array_rand($sources)],
                    ]
                );
                if ($role && ! $user->roles()->where('role_id', $role->id)->exists()) {
                    $user->roles()->attach($role->id);
                }
            }
        }
    }
}
