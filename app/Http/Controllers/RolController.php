<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Muestra una lista de los roles.
     */
    public function index()
    {
        $user = Auth::user();

        // Solo el administrador puede ver los roles
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para ver los roles.'], 403);
        }

        $roles = Role::with('permisos')->paginate(10);

        return response()->json($roles, 200);
    }

    /**
     * Almacena un nuevo rol en la base de datos.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Solo el administrador puede crear roles
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para crear roles.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $role = Role::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            if ($request->has('permisos')) {
                $role->permisos()->sync($request->permisos);
            }

            DB::commit();

            return response()->json($role->load('permisos'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el rol.'], 500);
        }
    }

    /**
     * Muestra un rol específico.
     */
    public function show($id)
    {
        $user = Auth::user();

        // Solo el administrador puede ver un rol
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para ver este rol.'], 403);
        }

        $role = Role::with('permisos')->find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado.'], 404);
        }

        return response()->json($role, 200);
    }

    /**
     * Actualiza un rol existente.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Solo el administrador puede actualizar roles
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para actualizar roles.'], 403);
        }

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $role->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            if ($request->has('permisos')) {
                $role->permisos()->sync($request->permisos);
            }

            DB::commit();

            return response()->json($role->load('permisos'), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el rol.'], 500);
        }
    }

    /**
     * Elimina un rol de la base de datos.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Solo el administrador puede eliminar roles
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar roles.'], 403);
        }

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            $role->permisos()->detach();
            $role->users()->detach();
            $role->delete();

            DB::commit();

            return response()->json(['message' => 'Rol eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el rol.'], 500);
        }
    }
}
