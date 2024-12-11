<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Asignacion;
use App\Models\Curso;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Crear roles
        $rolAdmin = Role::firstOrCreate(['nombre' => 'administrador']);
        $rolProfesor = Role::firstOrCreate(['nombre' => 'profesor']);
        $rolEstudiante = Role::firstOrCreate(['nombre' => 'estudiante']);

        // Crear usuario administrador
        $adminUser = User::updateOrCreate(
            ['email' => 'test@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Isabela#123'),
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$rolAdmin->id]);

        // Crear 2 usuarios con rol profesor
        for ($i = 1; $i <= 2; $i++) {
            $profesor = User::updateOrCreate(
                ['email' => "profesor{$i}@example.com"],
                [
                    'name' => "Profesor {$i}",
                    'password' => Hash::make('password123'),
                ]
            );
            $profesor->roles()->syncWithoutDetaching([$rolProfesor->id]);
        }

        // Crear 4 usuarios con rol estudiante
        for ($i = 1; $i <= 4; $i++) {
            $estudiante = User::updateOrCreate(
                ['email' => "estudiante{$i}@example.com"],
                [
                    'name' => "Estudiante {$i}",
                    'password' => Hash::make('password123'),
                ]
            );
            $estudiante->roles()->syncWithoutDetaching([$rolEstudiante->id]);
        }

        // Crear periodos
        $periodos = [
            ['nombre' => 'Periodo 1', 'fecha_inicio' => '2024-01-01', 'fecha_fin' => '2024-06-30'],
            ['nombre' => 'Periodo 2', 'fecha_inicio' => '2024-07-01', 'fecha_fin' => '2024-12-31'],
        ];

        foreach ($periodos as $periodo) {
            Periodo::updateOrCreate(['nombre' => $periodo['nombre']], $periodo);
        }

        // Crear materias
        $materias = [
            ['nombre' => 'Matemáticas', 'descripcion' => 'Curso de matemáticas avanzadas', 'nivel' => 'Avanzado'],
            ['nombre' => 'Historia', 'descripcion' => 'Curso de historia mundial', 'nivel' => 'Intermedio'],
            ['nombre' => 'Física', 'descripcion' => 'Curso de física básica', 'nivel' => 'Básico'],
        ];

        foreach ($materias as $materia) {
            Materia::updateOrCreate(['nombre' => $materia['nombre']], $materia);
        }

        // Crear asignaciones
        $profesores = User::whereHas('roles', function ($query) use ($rolProfesor) {
            $query->where('id', $rolProfesor->id);
        })->get();

        $materias = Materia::all();
        $periodos = Periodo::all();

        foreach ($materias as $index => $materia) {
            Asignacion::updateOrCreate(
                ['codigo_asignacion' => "ASG{$index}"],
                [
                    'profesor_id' => $profesores->random()->id,
                    'materia_id' => $materia->id,
                    'periodo_id' => $periodos->random()->id,
                ]
            );
        }

        // Crear cursos
        $asignaciones = Asignacion::all();
        foreach ($asignaciones as $index => $asignacion) {
            Curso::updateOrCreate(
                ['codigo' => "CUR{$index}"],
                [
                    'nombre' => "Curso {$index}",
                    'descripcion' => "Descripción del curso {$index}",
                    'creditos' => rand(3, 5),
                    'horas' => rand(40, 60),
                    'fecha_inicio' => '2024-01-15',
                    'fecha_fin' => '2024-06-15',
                    'asignacion_id' => $asignacion->id,
                ]
            );
        }

        // Asignar estudiantes a cursos
        $cursos = Curso::all();
        $estudiantes = User::whereHas('roles', function ($query) use ($rolEstudiante) {
            $query->where('id', $rolEstudiante->id);
        })->get();

        foreach ($cursos as $curso) {
            $curso->estudiantes()->sync($estudiantes->random(2)->pluck('id')->toArray());
        }
    }
}
