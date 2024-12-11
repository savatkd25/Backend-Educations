<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
//llamar al log
use Illuminate\Support\Facades\Log;

class CursoController extends Controller
{
    // Método index: Listar cursos con paginación
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($this->tieneRol('administrador')) {
            $cursos = Curso::with('asignacion')->paginate(10);
        } elseif ($this->tieneRol('profesor')) {
            // Los profesores solo ven los cursos de sus asignaciones
            $cursos = Curso::with('asignacion')
                ->whereHas('asignacion', function ($query) use ($user) {
                    $query->where('profesor_id', $user->id);
                })
                ->paginate(10);
        } else {
            return response()->json(['error' => 'No tienes permiso para ver los cursos.'], 403);
        }

        return response()->json($cursos, 200);
    }

    // Método store: Crear un nuevo curso
    public function store(Request $request)
    {
        $user = Auth::user();
        Log::info('Usuario autenticado: ' . $user->id);

        if (!$this->tieneRol('administrador')) {
            Log::warning('Permiso denegado para crear cursos. Usuario: ' . $user->id);
            return response()->json(['error' => 'No tienes permiso para crear cursos.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            //'codigo' => 'required|string|max:10|unique:curso,codigo',
            'descripcion' => 'nullable|string|max:255',
            'creditos' => 'required|integer|min:1',
            'horas' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date|after:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'asignacion_id' => 'required|exists:asignaciones,id',
        ]);

        if ($validator->fails()) {
            Log::error('Error: ' . json_encode($validator->errors()));
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();
            Log::info('Iniciando transacción para crear curso.');

            $curso = Curso::create($request->all());

            DB::commit();
            Log::info('Curso creado exitosamente: ' . $curso->id);

            return response()->json($curso, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear el curso: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear el curso.'], 500);
        }
    }

    // Método show: Mostrar un curso específico
    public function show($id)
    {
        $user = Auth::user();

        $curso = Curso::with('asignacion')->find($id);

        if (!$curso) {
            return response()->json(['error' => 'Curso no encontrado.'], 404);
        }

        if (
            $this->tieneRol('administrador') ||
            ($this->tieneRol('profesor') && $curso->asignacion->profesor_id == $user->id)
        ) {
            return response()->json($curso, 200);
        } else {
            return response()->json(['error' => 'No tienes permiso para ver este curso.'], 403);
        }
    }

    // Método update: Actualizar un curso existente
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para actualizar cursos.'], 403);
        }

        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json(['error' => 'Curso no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'codigo' => 'sometimes|string|max:10|unique:curso,codigo,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'creditos' => 'sometimes|integer|min:1',
            'horas' => 'sometimes|integer|min:1',
            'fecha_inicio' => 'sometimes|date|after:today',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'asignacion_id' => 'sometimes|exists:asignacion,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $curso->update($request->all());

            DB::commit();

            return response()->json($curso, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el curso.'], 500);
        }
    }

    // Método destroy: Eliminar un curso
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar cursos.'], 403);
        }

        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json(['error' => 'Curso no encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            $curso->delete();

            DB::commit();

            return response()->json(['message' => 'Curso eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el curso.'], 500);
        }
    }

    // Método privado tieneRol
    private function tieneRol($rolNombre)
    {
        $user = Auth::user();
        return $user->roles->contains('nombre', $rolNombre);
    }
}
