<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MateriaController extends Controller
{
    //funcion tieneRol
    private function tieneRol($rolNombre)
    {
        $user = Auth::user();

        // Obtener los roles asociados al usuario
        $roles = $user->roles;

        // Verificar si alguno de los roles coincide con el nombre del rol requerido
        foreach ($roles as $rol) {
            if ($rol->nombre === $rolNombre) {
                return true;
            }
        }

        return false;
    }

    /**
     * Muestra una lista paginada de las materias.
     */
    public function index()
    {
        $user = Auth::user();

        if ($this->tieneRol('administrador')) {
            // El administrador puede ver todas las materias
            $materias = Materia::paginate(10);
        } elseif ($this->tieneRol('profesor')) {
            // El profesor solo puede ver sus materias asignadas
            $materias = Materia::whereHas('asignaciones', function ($query) use ($user) {
                $query->where('profesor_id', $user->id);
            })->paginate(10);
        } else {
            return response()->json(['error' => 'No tienes permiso para ver las materias.'], 403);
        }

        return response()->json($materias, 200);
    }

    /**
     * Almacena una nueva materia en la base de datos.
     */
    public function store(Request $request)
    {
        // $user = Auth::user();

        // // Solo el administrador puede crear materias
        // if (!$this->tieneRol('administrador')) {
        //     return response()->json(['error' => 'No tienes permiso para crear materias.'], 403);
        // }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $materia = Materia::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'nivel' => $request->nivel,
            ]);

            DB::commit();

            return response()->json($materia, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear la materia.'], 500);
        }
    }

    /**
     * Muestra una materia específica.
     */
    public function show($id)
    {
        $user = Auth::user();

        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['error' => 'Materia no encontrada.'], 404);
        }

        if ($this->tieneRol('administrador')) {
            return response()->json($materia, 200);
        } elseif ($this->tieneRol('profesor')) {
            $tieneMateria = $materia->asignaciones()->where('profesor_id', $user->id)->exists();

            if ($tieneMateria) {
                return response()->json($materia, 200);
            } else {
                return response()->json(['error' => 'No tienes permiso para ver esta materia.'], 403);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para ver esta materia.'], 403);
        }
    }

    /**
     * Actualiza una materia existente.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Solo el administrador puede actualizar materias
        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para actualizar materias.'], 403);
        }

        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['error' => 'Materia no encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $materia->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'nivel' => $request->nivel,
            ]);

            DB::commit();

            return response()->json($materia, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la materia.'], 500);
        }
    }

    /**
     * Elimina una materia de la base de datos.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Solo el administrador puede eliminar materias
        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar materias.'], 403);
        }

        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['error' => 'Materia no encontrada.'], 404);
        }

        try {
            DB::beginTransaction();

            $materia->delete();

            DB::commit();

            return response()->json(['message' => 'Materia eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la materia.'], 500);
        }
    }
}