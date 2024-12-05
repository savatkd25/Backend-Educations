<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoEstudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CursoEstudianteController extends Controller
{
    // Verificar rol
    private function tieneRol($rolNombre)
    {
        $user = Auth::user();
        foreach ($user->roles as $rol) {
            if ($rol->nombre === $rolNombre) {
                return true;
            }
        }
        return false;
    }

    // Método index: Listar todos los estudiantes inscritos en un curso
    public function index(Request $request, $cursoId)
    {
        $user = Auth::user();

        if (!$this->tieneRol('administrador') && !$this->tieneRol('profesor')) {
            return response()->json(['error' => 'No tienes permiso para ver los estudiantes.'], 403);
        }

        $cursoEstudiantes = CursoEstudiante::with(['curso', 'estudiante'])
            ->where('curso_id', $cursoId)
            ->paginate(10);

        return response()->json($cursoEstudiantes, 200);
    }

    // Método store: Inscribir un estudiante en un curso
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para inscribir estudiantes.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'curso_id' => 'required|exists:curso,id',
            'estudiante_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $cursoEstudiante = CursoEstudiante::create([
                'curso_id' => $request->curso_id,
                'estudiante_id' => $request->estudiante_id,
            ]);

            DB::commit();

            return response()->json($cursoEstudiante, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al inscribir el estudiante.'], 500);
        }
    }

    // Método show: Mostrar información de un estudiante en un curso específico
    public function show($id)
    {
        $user = Auth::user();

        $cursoEstudiante = CursoEstudiante::with(['curso', 'estudiante'])->find($id);

        if (!$cursoEstudiante) {
            return response()->json(['error' => 'Registro no encontrado.'], 404);
        }

        if ($this->tieneRol('administrador') || $this->tieneRol('profesor')) {
            return response()->json($cursoEstudiante, 200);
        } else {
            return response()->json(['error' => 'No tienes permiso para ver este registro.'], 403);
        }
    }

    // Método destroy: Eliminar la inscripción de un estudiante en un curso
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para eliminar registros.'], 403);
        }

        $cursoEstudiante = CursoEstudiante::find($id);

        if (!$cursoEstudiante) {
            return response()->json(['error' => 'Registro no encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            $cursoEstudiante->delete();

            DB::commit();

            return response()->json(['message' => 'Registro eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el registro.'], 500);
        }
    }
}
