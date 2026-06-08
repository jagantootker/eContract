<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters — respect FK dependencies:
     *   roles → jabatan → users → admin user (needs roles)
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            JabatanSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
