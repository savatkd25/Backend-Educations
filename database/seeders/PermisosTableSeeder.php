<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisosTableSeeder extends Seeder
{
    public function run()
    {
        // Permisos Globales
        $permisosGlobales = [
            ['nombre' => 'gestionar_usuarios', 'descripcion' => 'Gestionar todos los usuarios del sistema'],
            ['nombre' => 'gestionar_roles', 'descripcion' => 'Gestionar los roles y permisos'],
            ['nombre' => 'gestionar_periodos', 'descripcion' => 'Crear, editar y eliminar periodos académicos'],
            ['nombre' => 'ver_todas_las_materias', 'descripcion' => 'Ver todas las materias del sistema'],
            ['nombre' => 'ver_todas_las_notas', 'descripcion' => 'Ver todas las notas de los estudiantes'],
        ];

        // Permisos Específicos
        $permisosEspecificos = [
            ['nombre' => 'ver_mis_materias', 'descripcion' => 'Ver las materias asignadas al usuario'],
            ['nombre' => 'asignar_tareas', 'descripcion' => 'Asignar tareas a los estudiantes'],
            ['nombre' => 'ver_mis_notas', 'descripcion' => 'Ver las notas del usuario'],
        ];

        // Insertar permisos globales
        foreach ($permisosGlobales as $permiso) {
            Permiso::create($permiso);
        }

        // Insertar permisos específicos
        foreach ($permisosEspecificos as $permiso) {
            Permiso::create($permiso);
        }
    }
}
