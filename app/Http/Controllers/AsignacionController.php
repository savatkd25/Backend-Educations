<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AsignacionController extends Controller
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

    // Método index: Listar asignaciones con paginación
    public function index(Request $request)
    {
        $user = Auth::user();

        // Verificar permiso: Solo el administrador puede ver todas las asignaciones
        if ($this->tieneRol('administrador')) {
            $asignaciones = Asignacion::with(['profesor', 'materia', 'periodo'])->paginate(10);
        } elseif ($this->tieneRol('profesor')) {
            // El profesor solo ve sus asignaciones
            $asignaciones = Asignacion::with(['profesor', 'materia', 'periodo'])
                ->where('profesor_id', $user->id)
                ->paginate(10);
        } else {
            // Si no tiene permiso, devolver error
            return response()->json(['error' => 'No tienes permiso para ver las asignaciones.'], 403);
        }

        return response()->json($asignaciones, 200);
    }

    // Método store: Crear una nueva asignación
    public function store(Request $request)
    {
        $user = Auth::user();

        // Verificar permiso: Solo el administrador puede crear asignaciones
        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para crear asignaciones.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'profesor_id' => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            // Generar código único para la asignación
            $codigoAsignacion = 'ASG-' . uniqid();

            $asignacion = Asignacion::create([
                'codigo_asignacion' => $codigoAsignacion,
                'profesor_id' => $request->profesor_id,
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
            ]);

            DB::commit();

            return response()->json($asignacion, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear la asignación.'], 500);
        }
    }

    // Método show: Mostrar una asignación específica
    public function show($id)
    {
        $user = Auth::user();

        $asignacion = Asignacion::with(['profesor', 'materia', 'periodo'])->find($id);

        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada.'], 404);
        }

        // Verificar permiso
        if ($this->tieneRol('administrador') || ($this->tieneRol('profesor') && $asignacion->profesor_id == $user->id)) {
            return response()->json($asignacion, 200);
        } else {
            return response()->json(['error' => 'No tienes permiso para ver esta asignación.'], 403);
        }
    }

    // Método update: Actualizar una asignación existente
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Verificar permiso: Solo el administrador puede actualizar asignaciones
        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para actualizar asignaciones.'], 403);
        }

        $asignacion = Asignacion::find($id);

        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'profesor_id' => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $asignacion->update([
                'profesor_id' => $request->profesor_id,
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
            ]);

            DB::commit();

            return response()->json($asignacion, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar la asignación.'], 500);
        }
    }

    // Método destroy: Eliminar una asignación
    public function destroy($id)
    {
        $user = Auth::user();

        // Verificar permiso: Solo el administrador puede eliminar asignaciones
        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar asignaciones.'], 403);
        }

        $asignacion = Asignacion::find($id);

        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada.'], 404);
        }

        try {
            DB::beginTransaction();

            $asignacion->delete();

            DB::commit();

            return response()->json(['message' => 'Asignación eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la asignación.'], 500);
        }
    }
}