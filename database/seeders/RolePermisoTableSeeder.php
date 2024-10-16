<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;

class RolePermisoTableSeeder extends Seeder
{
    public function run()
    {
        // Obtener los roles
        $adminRole = Role::where('nombre', 'administrador')->first();
        $profesorRole = Role::where('nombre', 'profesor')->first();
        $estudianteRole = Role::where('nombre', 'estudiante')->first();

        // Obtener todos los permisos
        $permisos = Permiso::all();

        // Asignar todos los permisos al administrador
        $adminRole->permisos()->attach($permisos);

        // Permisos para el profesor
        $permisosProfesor = Permiso::whereIn('nombre', [
            'ver_mis_materias',
            'asignar_tareas',
        ])->get();

        $profesorRole->permisos()->attach($permisosProfesor);

        // Permisos para el estudiante
        $permisosEstudiante = Permiso::whereIn('nombre', [
            'ver_mis_materias',
            'ver_mis_notas',
        ])->get();

        $estudianteRole->permisos()->attach($permisosEstudiante);
    }
}
