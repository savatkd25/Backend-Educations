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

    public function verCalificaciones()
    {
        $user = Auth::user();

        if ($this->tieneRol('profesor')) {
            // Obtener calificaciones de todos los estudiantes
            $calificaciones = Entrega::with('estudiante')
                ->whereNotNull('calificacion') // Solo entregas calificadas
                ->get()
                ->groupBy('estudiante_id'); // Agrupar por estudiante

            $datos = $calificaciones->map(function ($entregas, $estudianteId) {
                $promedio = $entregas->avg('calificacion'); // Calcular promedio
                return [
                    'estudiante' => $entregas->first()->estudiante->name,
                    'entregas' => $entregas->map(function ($entrega) {
                        return [
                            'tarea_id' => $entrega->tarea_id,
                            'calificacion' => $entrega->calificacion,
                        ];
                    }),
                    'promedio_final' => round($promedio, 2),
                ];
            });

            return response()->json([
                'datos' => $datos,
                'esProfesor' => true,
            ], 200);
        }

        if ($this->tieneRol('estudiante')) {
            // Obtener calificaciones del estudiante autenticado
            $entregas = Entrega::where('estudiante_id', $user->id)
                ->whereNotNull('calificacion')
                ->get();

            $promedio = $entregas->avg('calificacion'); // Calcular promedio general

            return response()->json([
                'entregas' => $entregas->map(function ($entrega) {
                    return [
                        'tarea_id' => $entrega->tarea_id,
                        'calificacion' => $entrega->calificacion,
                    ];
                }),
                'promedio' => round($promedio, 2),
                'esProfesor' => false,
            ], 200);
        }

        return response()->json([
            'error' => 'No tienes permisos para ver esta información.',
        ], 403);
    }


    // Obtener entregas de una tarea específica
    public function obtenerEntregasPorTarea($tareaId)
    {
        // Consultar las entregas relacionadas con la tarea
        $entregas = Entrega::where('tarea_id', $tareaId)
            ->with([
                'estudiante:id,name', // Asegúrate de que la relación 'estudiante' esté correctamente definida
            ])
            ->select('id', 'tarea_id', 'estudiante_id', 'archivo', 'calificacion') // Incluir los campos relevantes
            ->get();

        // Verificar si hay entregas
        if ($entregas->isEmpty()) {
            return response()->json(['message' => 'No hay entregas registradas para esta tarea'], 404);
        }

        // Retornar las entregas con sus relaciones
        return response()->json($entregas);
    }

    //Funcion para descargar archivos del public/storage/entrega
    public function descargarArchivo($nombreArchivo)
    {
        $rutaArchivo = storage_path('app/public/entregas/' . $nombreArchivo);

        if (!file_exists($rutaArchivo)) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        return response()->download($rutaArchivo);
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
        // if (!$this->tieneRol('profesor') && !$this->tieneRol('administrador')) {
        //     return response()->json(['error' => 'No tienes permiso para listar entregas.'], 403);
        // }

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
