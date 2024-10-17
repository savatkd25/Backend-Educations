<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermisosTableSeeder::class,
            RolesTableSeeder::class,
            RolePermisoTableSeeder::class,
            AdminUserSeeder::class,

        ]);
    }
}
