<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntregaController extends Controller
{
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
    // Método para calcular el promedio de calificaciones de un estudiante
    public function promedioCalificaciones($estudianteId)
    {
        $entregas = Entrega::where('estudiante_id', $estudianteId)
            ->whereNotNull('calificacion') // Solo considerar entregas calificadas
            ->get();

        if ($entregas->isEmpty()) {
            return response()->json(['message' => 'No hay entregas calificadas'], 404);
        }

        $sumaCalificaciones = $entregas->sum('calificacion');
        $totalEntregas = $entregas->count();

        $promedio = $sumaCalificaciones / $totalEntregas;

        $resultado = $promedio >= 14 ? 'Aprobado' : 'Reprobado';

        return response()->json([
            'promedio' => round($promedio, 2),
            'resultado' => $resultado,
            'entregas_calificadas' => $totalEntregas
        ]);
    }

    // Obtener entregas de una tarea específica
    public function obtenerEntregasPorTarea($tareaId)
    {
        $entregas = Entrega::where('tarea_id', $tareaId)
            ->with('estudiante:id,name') // Obtener el nombre del estudiante
            ->get();

        if ($entregas->isEmpty()) {
            return response()->json(['message' => 'No hay entregas registradas para esta tarea'], 404);
        }

        return response()->json($entregas);
    }

    // Método para calificar una entrega
    public function calificarEntregas(Request $request)
    {
        $request->validate([
            'calificaciones' => 'required|array',
            'calificaciones.*.id' => 'required|exists:entrega,id',
            'calificaciones.*.calificacion' => 'required|numeric|min:0|max:20',
        ]);

        foreach ($request->calificaciones as $calificacionData) {
            $entrega = Entrega::find($calificacionData['id']);
            $entrega->calificacion = $calificacionData['calificacion'];
            $entrega->save();
        }

        return response()->json(['message' => 'Calificaciones actualizadas con éxito']);
    }


    public function index($tareaId)
    {
        if (!$this->tieneRol('profesor') && !$this->tieneRol('administrador')) {
            return response()->json(['error' => 'No tienes permiso para listar entregas.'], 403);
        }

        $entregas = Entrega::where('tarea_id', $tareaId)->paginate(10);
        return response()->json($entregas, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tarea_id' => 'required|exists:tarea,id',
            'estudiante_id' => 'required|exists:users,id',
            'comentarios' => 'nullable|string',
            'archivo' => 'required|file|mimes:pdf,docx,txt|max:2048', // Validación del archivo
        ]);

        $rutaArchivo = null;

        // Subir el archivo
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $rutaArchivo = $archivo->store('entregas', 'public'); // Guardar en carpeta 'public/entregas'
        }

        // Crear la entrega
        $entrega = Entrega::create([
            'tarea_id' => $request->input('tarea_id'),
            'estudiante_id' => $request->input('estudiante_id'),
            'comentarios' => $request->input('comentarios'),
            'archivo' => $rutaArchivo, // Guardamos la ruta del archivo
        ]);

        return response()->json($entrega, 201);
    }


    public function show($id)
    {
        $entrega = Entrega::find($id);

        if (!$entrega) {
            return response()->json(['error' => 'Entrega no encontrada.'], 404);
        }

        $data = $entrega->toArray();

        // Si hay archivo, agregamos su URL
        if ($entrega->archivo) {
            $data['archivo_url'] = asset('storage/' . $entrega->archivo);
        }

        return response()->json($data, 200);
    }


    public function destroy($id)
    {
        if (!$this->tieneRol('profesor')) {
            return response()->json(['error' => 'No tienes permiso para eliminar entregas.'], 403);
        }

        $entrega = Entrega::find($id);

        if (!$entrega) {
            return response()->json(['error' => 'Entrega no encontrada.'], 404);
        }

        $entrega->delete();
        return response()->json(['message' => 'Entrega eliminada exitosamente.'], 200);
    }
}
