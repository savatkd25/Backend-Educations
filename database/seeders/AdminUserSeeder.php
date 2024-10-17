<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el usuario ya existe
        $user = User::where('email', 'test@admin.com')->first();

        if (!$user) {
            // Crear el usuario administrador
            $user = User::create([
                'name'     => 'Admin',
                'email'    => 'test@admin.com',
                'password' => Hash::make('Isabela#123'),
                // No incluimos 'rol_id' porque no existe en la tabla 'users'
            ]);
        } else {
            // Opcionalmente, actualizar el usuario existente
            $user->update([
                'name'     => 'Admin',
                'password' => Hash::make('Isabela#123'),
            ]);
        }

        // Asignar el rol de administrador al usuario
        $rolAdmin = Role::where('nombre', 'administrador')->first();

        if (!$rolAdmin) {
            // Si no se encuentra el rol 'admin', puedes crearlo o lanzar una excepción
            $rolAdmin = Role::create(['nombre' => 'administrador']);
        }

        // Asignar el rol al usuario
        $user->roles()->syncWithoutDetaching([$rolAdmin->id]);
    }
}
