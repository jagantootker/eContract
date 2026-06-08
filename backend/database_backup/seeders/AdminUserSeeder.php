<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        $admin = User::updateOrCreate(
            ['ic_number' => '000000000000'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@ekontrak.gov.my',
                'password' => Hash::make('Admin@1234!'),
                'source'   => 'BTM',
                'is_active' => true,
            ]
        );

        // Assign 'admin' role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole && ! $admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }

        $this->command->info("Admin user seeded. IC: 000000000000 | Email: admin@ekontrak.gov.my");
        $this->command->warn("⚠  Change the default password after first login!");
    }
}
