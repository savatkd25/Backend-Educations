<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{
    /**
     * Muestra una lista de los permisos.
     */
    public function index()
    {
        $user = Auth::user();

        // Solo el administrador puede ver los permisos
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para ver los permisos.'], 403);
        }

        $permisos = Permiso::paginate(10);

        return response()->json($permisos, 200);
    }

    /**
     * Almacena un nuevo permiso en la base de datos.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Solo el administrador puede crear permisos
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para crear permisos.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:permisos,nombre',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $permiso = Permiso::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            DB::commit();

            return response()->json($permiso, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el permiso.'], 500);
        }
    }

    /**
     * Muestra un permiso específico.
     */
    public function show($id)
    {
        $user = Auth::user();

        // Solo el administrador puede ver un permiso
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para ver este permiso.'], 403);
        }

        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['error' => 'Permiso no encontrado.'], 404);
        }

        return response()->json($permiso, 200);
    }

    /**
     * Actualiza un permiso existente.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Solo el administrador puede actualizar permisos
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para actualizar permisos.'], 403);
        }

        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['error' => 'Permiso no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:permisos,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $permiso->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            DB::commit();

            return response()->json($permiso, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el permiso.'], 500);
        }
    }

    /**
     * Elimina un permiso de la base de datos.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Solo el administrador puede eliminar permisos
        if (!$user->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar permisos.'], 403);
        }

        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['error' => 'Permiso no encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            $permiso->delete();

            DB::commit();

            return response()->json(['message' => 'Permiso eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el permiso.'], 500);
        }
    }
}
