<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['nombre' => 'administrador', 'descripcion' => 'Administrador del sistema'],
            ['nombre' => 'profesor', 'descripcion' => 'Profesor de la institución'],
            ['nombre' => 'estudiante', 'descripcion' => 'Estudiante de la institución'],
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
    }
}
